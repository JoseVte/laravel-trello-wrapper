<?php

use Semaio\TrelloApi\Client;
use Semaio\TrelloApi\Manager;
use PHPUnit\Framework\TestCase;
use Semaio\TrelloApi\Api\BoardApi;
use Semaio\TrelloApi\Api\ActionApi;
use Semaio\TrelloApi\Api\MemberApi;
use Illuminate\Contracts\Config\Repository;
use Semaio\TrelloApi\Api\Board\BoardLabelsApi;
use Semaio\TrelloApi\Api\Member\MemberBoardsApi;
use Semaio\TrelloApi\Api\Board\BoardCardListsApi;
use Semaio\TrelloApi\Api\Member\MemberOrganizationsApi;

class WrapperTest extends TestCase
{
    /**
     * @test
     */
    public function shouldHaveToPassRepoToWrapperConstructor(): void
    {
        $wrapper = new \LaravelTrello\Wrapper($this->getRepositoryMock());
        $this->assertInstanceOf(Repository::class, $wrapper->config);
    }

    /**
     * @test
     */
    public function shouldGetManagerFromWrapper(): void
    {
        $wrapper = new \LaravelTrello\Wrapper($this->getRepositoryMock());
        $this->assertInstanceOf(Manager::class, $wrapper->manager());
    }

    /**
     * @test
     */
    public function shouldReturnSameManagerInstanceOnSubsequentCalls(): void
    {
        $wrapper = new \LaravelTrello\Wrapper($this->getRepositoryMock());
        $this->assertSame($wrapper->manager(), $wrapper->manager());
    }

    /**
     * @test
     */
    public function shouldGetClientFromWrapper(): void
    {
        $wrapper = new \LaravelTrello\Wrapper($this->getRepositoryMock());
        $this->assertInstanceOf(Client::class, $wrapper->getClient());
    }

    /**
     * @test
     */
    public function shouldSetClientAndReturnIt(): void
    {
        $wrapper = new \LaravelTrello\Wrapper($this->getRepositoryMock());
        $client = $this->createMock(Client::class);

        $returned = $wrapper->setClient($client);

        $this->assertSame($client, $returned);
        $this->assertSame($client, $wrapper->getClient());
    }

    /**
     * @test
     */
    public function shouldRebuildClientWhenSettingUserToken(): void
    {
        $wrapper = new \LaravelTrello\Wrapper($this->getRepositoryMock());
        $original = $wrapper->getClient();

        $wrapper->setUserToken('new-user-token');

        $this->assertInstanceOf(Client::class, $wrapper->getClient());
        $this->assertNotSame($original, $wrapper->getClient());
    }

    /**
     * @test
     */
    public function shouldProxyKnownClientMethodsViaMagicCall(): void
    {
        $wrapper = new \LaravelTrello\Wrapper($this->getRepositoryMock());
        $client = $this->createMock(Client::class);
        $actionApi = $this->createMock(ActionApi::class);

        $client->expects($this->once())
            ->method('getActionApi')
            ->willReturn($actionApi);

        $wrapper->setClient($client);

        $this->assertSame($actionApi, $wrapper->getActionApi());
    }

    /**
     * @test
     */
    public function shouldNotGetMagicApiInstance(): void
    {
        $this->expectException(TypeError::class);
        $wrapper = new \LaravelTrello\Wrapper($this->getRepositoryMock());
        $wrapper->doNotExist();
    }

    /**
     * @test
     */
    public function shouldResolveOrganizationIdByName(): void
    {
        $wrapper = $this->wrapperWithMockedClient(
            $this->mockClient([
                'organizations' => [
                    ['id' => 'org-1', 'name' => 'Acme'],
                    ['id' => 'org-2', 'name' => 'Globex'],
                ],
            ])
        );

        $this->assertSame('org-2', $wrapper->getObjectId('organization', 'Globex'));
    }

    /**
     * @test
     */
    public function shouldReturnFalseWhenOrganizationNotFound(): void
    {
        $wrapper = $this->wrapperWithMockedClient(
            $this->mockClient(['organizations' => [['id' => 'org-1', 'name' => 'Acme']]])
        );

        $this->assertFalse($wrapper->getObjectId('organization', 'Missing'));
    }

    /**
     * @test
     */
    public function shouldReturnFalseForUnknownObjectType(): void
    {
        $wrapper = new \LaravelTrello\Wrapper($this->getRepositoryMock());
        $this->assertFalse($wrapper->getObjectId('unknown', 'whatever'));
    }

    /**
     * @test
     */
    public function shouldResolveBoardIdMatchingOrganization(): void
    {
        $wrapper = $this->wrapperWithMockedClient(
            $this->mockClient([
                'organizations' => [['id' => 'org-1', 'name' => 'Acme']],
                'boards' => [
                    ['id' => 'board-1', 'name' => 'Main', 'idOrganization' => 'org-1'],
                    ['id' => 'board-2', 'name' => 'Other', 'idOrganization' => 'org-1'],
                ],
            ]),
            ['organization' => 'Acme']
        );

        $this->assertSame('board-1', $wrapper->getObjectId('board', 'Main'));
    }

    /**
     * @test
     */
    public function shouldFallBackToSharedBoardWithDifferentOrganization(): void
    {
        $wrapper = $this->wrapperWithMockedClient(
            $this->mockClient([
                'organizations' => [['id' => 'org-1', 'name' => 'Acme']],
                'boards' => [
                    ['id' => 'shared-1', 'name' => 'Shared', 'idOrganization' => 'org-other'],
                ],
            ]),
            ['organization' => 'Acme']
        );

        $this->assertSame('shared-1', $wrapper->getObjectId('board', 'Shared'));
    }

    /**
     * @test
     */
    public function shouldResolveListIdForBoard(): void
    {
        $wrapper = $this->wrapperWithMockedClient(
            $this->mockClient([
                'organizations' => [['id' => 'org-1', 'name' => 'Acme']],
                'boards' => [['id' => 'board-1', 'name' => 'Main', 'idOrganization' => 'org-1']],
                'lists' => [
                    'board-1' => [
                        ['id' => 'list-1', 'name' => 'Today'],
                        ['id' => 'list-2', 'name' => 'Done'],
                    ],
                ],
            ]),
            ['organization' => 'Acme', 'board' => 'Main']
        );

        $this->assertSame('list-2', $wrapper->getObjectId('list', 'Done'));
    }

    /**
     * @test
     */
    public function shouldResolveLabelIdForBoard(): void
    {
        $wrapper = $this->wrapperWithMockedClient(
            $this->mockClient([
                'organizations' => [['id' => 'org-1', 'name' => 'Acme']],
                'boards' => [['id' => 'board-1', 'name' => 'Main', 'idOrganization' => 'org-1']],
                'labels' => [
                    'board-1' => [
                        ['id' => 'label-1', 'name' => 'urgent'],
                        ['id' => 'label-2', 'name' => 'bug'],
                    ],
                ],
            ]),
            ['organization' => 'Acme', 'board' => 'Main']
        );

        $this->assertSame('label-2', $wrapper->getObjectId('label', 'bug'));
    }

    /**
     * @test
     */
    public function shouldCacheOrganizationsAfterFirstLookup(): void
    {
        $organizationsApi = $this->createMock(MemberOrganizationsApi::class);
        $organizationsApi->expects($this->once())
            ->method('all')
            ->with('me')
            ->willReturn([['id' => 'org-1', 'name' => 'Acme']]);

        $memberApi = $this->createMock(MemberApi::class);
        $memberApi->method('organizations')->willReturn($organizationsApi);

        $client = $this->createMock(Client::class);
        $client->method('getMemberApi')->willReturn($memberApi);

        $wrapper = $this->wrapperWithMockedClient($client);

        $wrapper->getObjectId('organization', 'Acme');
        $wrapper->getObjectId('organization', 'Acme');
    }

    /**
     * @test
     */
    public function shouldDelegateDefaultGettersToConfiguredNames(): void
    {
        $wrapper = $this->wrapperWithMockedClient(
            $this->mockClient([
                'organizations' => [['id' => 'org-1', 'name' => 'Acme']],
                'boards' => [['id' => 'board-1', 'name' => 'Main', 'idOrganization' => 'org-1']],
                'lists' => [
                    'board-1' => [['id' => 'list-1', 'name' => 'Today']],
                ],
            ]),
            ['organization' => 'Acme', 'board' => 'Main', 'list' => 'Today']
        );

        $this->assertSame('org-1', $wrapper->getDefaultOrganizationId());
        $this->assertSame('board-1', $wrapper->getDefaultBoardId());
        $this->assertSame('list-1', $wrapper->getDefaultListId());
    }

    /**
     * Build a Wrapper backed by a Repository that returns the given config map.
     *
     * @param array<string, string|null> $config
     */
    private function wrapperWithMockedClient(Client $client, array $config = []): \LaravelTrello\Wrapper
    {
        $wrapper = new \LaravelTrello\Wrapper($this->getRepositoryMock($config));
        $wrapper->setClient($client);

        return $wrapper;
    }

    /**
     * Build a mocked Client that returns the supplied datasets from
     * the chains used by Wrapper::getObjectId().
     *
     * @param array{
     *     organizations?: array<int, array<string, string>>,
     *     boards?: array<int, array<string, string>>,
     *     lists?: array<string, array<int, array<string, string>>>,
     *     labels?: array<string, array<int, array<string, string>>>
     * } $data
     */
    private function mockClient(array $data): Client
    {
        $client = $this->createMock(Client::class);

        if (array_key_exists('organizations', $data) || array_key_exists('boards', $data)) {
            $memberApi = $this->createMock(MemberApi::class);

            if (array_key_exists('organizations', $data)) {
                $organizationsApi = $this->createMock(MemberOrganizationsApi::class);
                $organizationsApi->method('all')->with('me')->willReturn($data['organizations']);
                $memberApi->method('organizations')->willReturn($organizationsApi);
            }

            if (array_key_exists('boards', $data)) {
                $boardsApi = $this->createMock(MemberBoardsApi::class);
                $boardsApi->method('all')->with('me')->willReturn($data['boards']);
                $memberApi->method('boards')->willReturn($boardsApi);
            }

            $client->method('getMemberApi')->willReturn($memberApi);
        }

        if (array_key_exists('lists', $data) || array_key_exists('labels', $data)) {
            $boardApi = $this->createMock(BoardApi::class);

            if (array_key_exists('lists', $data)) {
                $listsApi = $this->createMock(BoardCardListsApi::class);
                $listsApi->method('all')->willReturnCallback(
                    static fn (string $boardId): array => $data['lists'][$boardId] ?? []
                );
                $boardApi->method('lists')->willReturn($listsApi);
            }

            if (array_key_exists('labels', $data)) {
                $labelsApi = $this->createMock(BoardLabelsApi::class);
                $labelsApi->method('all')->willReturnCallback(
                    static fn (string $boardId): array => $data['labels'][$boardId] ?? []
                );
                $boardApi->method('labels')->willReturn($labelsApi);
            }

            $client->method('getBoardApi')->willReturn($boardApi);
        }

        return $client;
    }

    /**
     * @param array<string, string|null> $values
     */
    private function getRepositoryMock(array $values = []): Repository
    {
        $mock = $this->getMockBuilder(Repository::class)->getMock();

        $mock->method('get')->willReturnCallback(
            static fn (string $key): string => match ($key) {
                'trello.organization' => $values['organization'] ?? '',
                'trello.board' => $values['board'] ?? '',
                'trello.list' => $values['list'] ?? '',
                default => '',
            }
        );

        return $mock;
    }
}
