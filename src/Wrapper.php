<?php

namespace LaravelTrello;

use Semaio\TrelloApi\Client;
use Semaio\TrelloApi\Manager;
use Semaio\TrelloApi\Api\CardApi;
use Semaio\TrelloApi\Api\BoardApi;
use Semaio\TrelloApi\Api\TokenApi;
use Semaio\TrelloApi\Api\ActionApi;
use Semaio\TrelloApi\Api\MemberApi;
use Semaio\TrelloApi\ClientBuilder;
use Semaio\TrelloApi\Api\WebhookApi;
use Semaio\TrelloApi\Api\CardListApi;
use Semaio\TrelloApi\Api\ChecklistApi;
use Semaio\TrelloApi\Api\NotificationApi;
use Semaio\TrelloApi\Api\OrganizationApi;
use Illuminate\Contracts\Config\Repository;
use Psr\Http\Client\ClientInterface as HttpClientInterface;

/**
 * @method ActionApi       getActionApi()
 * @method BoardApi        getBoardApi()
 * @method CardApi         getCardApi()
 * @method CardListApi     getCardListApi()
 * @method ChecklistApi    getChecklistApi()
 * @method MemberApi       getMemberApi()
 * @method NotificationApi getNotificationApi()
 * @method OrganizationApi getOrganizationApi()
 * @method TokenApi        getTokenApi()
 * @method WebhookApi      getWebhookApi()
 */
class Wrapper
{
    /**
     * Trello client instance.
     *
     * @var Client
     */
    private Client $client;

    /**
     * Trello client builder.
     *
     * @var ClientBuilder
     */
    private ClientBuilder $clientBuilder;

    /**
     * Trello manager instance.
     *
     * @var Manager
     */
    private Manager $manager;

    /**
     * Client cache.
     *
     * @var array
     */
    private array $cache;

    public function __construct(public Repository $config, ?HttpClientInterface $httpClient = null)
    {
        // Set the config

        // Make the client instance
        $this->clientBuilder = new ClientBuilder();
        if ($httpClient !== null) {
            $this->clientBuilder->setHttpClient($httpClient);
        }
        $this->client = $this->clientBuilder->build($this->config->get('trello.api_key'), $this->config->get('trello.api_token'));
    }

    public function setUserToken(string $userApiToken): void
    {
        $this->client = $this->clientBuilder->build($this->config->get('trello.api_key'), $userApiToken);
    }

    public function manager(): Manager
    {
        if (!isset($this->manager)) {
            $this->manager = new Manager($this->client);
        }

        return $this->manager;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): Client
    {
        return $this->client = $client;
    }

    public function getObjectId(string $type, string $name, array $options = [])
    {
        switch ($type) {
            case 'organization':
                if (!isset($this->cache['organizations'])) {
                    $this->cache['organizations'] = $this->client->getMemberApi()->organizations()->all('me');
                }

                foreach ($this->cache['organizations'] as $organization) {
                    if ($name === $organization['name']) {
                        return $organization['id'];
                    }
                }
                break;
            case 'board':
                if (!isset($options['organization'])) {
                    $options['organization'] = $this->config->get('trello.organization');
                }
                $organizationId = $this->getObjectId('organization', $options['organization']);

                if (!isset($this->cache['boards'])) {
                    $this->cache['boards'] = $this->client->getMemberApi()->boards()->all('me');
                }

                foreach ($this->cache['boards'] as $board) {
                    if ($name === $board['name'] && $organizationId === $board['idOrganization']) {
                        return $board['id'];
                    }
                }

                // Workaround for shared boards
                foreach ($this->cache['boards'] as $board) {
                    if ($name === $board['name']) {
                        return $board['id'];
                    }
                }
                break;
            case 'list':
                if (!isset($options['organization'])) {
                    $options['organization'] = $this->config->get('trello.organization');
                }
                $organizationId = $this->getObjectId('organization', $options['organization']);

                if (!isset($options['board'])) {
                    $options['board'] = $this->config->get('trello.board');
                }
                $boardId = $this->getObjectId('board', $options['board'], ['organization' => $organizationId]);

                if (!isset($this->cache['lists'][$boardId])) {
                    $this->cache['lists'][$boardId] = $this->client->getBoardApi()->lists()->all($boardId);
                }

                foreach ($this->cache['lists'][$boardId] as $list) {
                    if ($name === $list['name']) {
                        return $list['id'];
                    }
                }

                break;
            case 'label':
                if (!isset($options['organization'])) {
                    $options['organization'] = $this->config->get('trello.organization');
                }
                $organizationId = $this->getObjectId('organization', $options['organization']);

                if (!isset($options['board'])) {
                    $options['board'] = $this->config->get('trello.board');
                }
                $boardId = $this->getObjectId('board', $options['board'], ['organization' => $organizationId]);

                if (!isset($this->cache['labels'][$boardId])) {
                    $this->cache['labels'][$boardId] = $this->client->getBoardApi()->labels()->all($boardId);
                }

                foreach ($this->cache['labels'][$boardId] as $label) {
                    if ($name === $label['name']) {
                        return $label['id'];
                    }
                }

                break;
        }

        return false;
    }

    public function getDefaultOrganizationId()
    {
        return $this->getObjectId('organization', $this->config->get('trello.organization'));
    }

    public function getDefaultBoardId()
    {
        return $this->getObjectId('board', $this->config->get('trello.board'));
    }

    public function getDefaultListId()
    {
        return $this->getObjectId('list', $this->config->get('trello.list'));
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->client, $name], $arguments);
    }
}
