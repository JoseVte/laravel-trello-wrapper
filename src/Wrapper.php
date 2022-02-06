<?php

namespace LaravelTrello;

use Semaio\TrelloApi\Manager;
use Semaio\TrelloApi\ClientBuilder;
use Illuminate\Contracts\Config\Repository;

/**
 * @method \Semaio\TrelloApi\Api\ActionApi getActionApi()
 * @method \Semaio\TrelloApi\Api\BoardApi getBoardApi()
 * @method \Semaio\TrelloApi\Api\CardApi getCardApi()
 * @method \Semaio\TrelloApi\Api\CardListApi getCardListApi()
 * @method \Semaio\TrelloApi\Api\ChecklistApi getChecklistApi()
 * @method \Semaio\TrelloApi\Api\MemberApi getMemberApi()
 * @method \Semaio\TrelloApi\Api\NotificationApi getNotificationApi()
 * @method \Semaio\TrelloApi\Api\OrganizationApi getOrganizationApi()
 * @method \Semaio\TrelloApi\Api\TokenApi getTokenApi()
 * @method \Semaio\TrelloApi\Api\WebhookApi getWebhookApi()
 */
class Wrapper
{
    /**
     * Config instance.
     *
     * @var \Illuminate\Contracts\Config\Repository
     */
    public $config;

    /**
     * Trello client instance.
     *
     * @var \Semaio\TrelloApi\Client
     */
    private $client;

    /**
     * Trello client builder.
     *
     * @var \Semaio\TrelloApi\ClientBuilder
     */
    private $clientBuilder;

    /**
     * Trello manager instance.
     *
     * @var \Semaio\TrelloApi\Manager
     */
    private $manager;

    /**
     * Client cache.
     *
     * @var array
     */
    private $cache;

    public function __construct(Repository $config)
    {
        // Set the config
        $this->config = $config;

        // Make the client instance
        $this->clientBuilder = new ClientBuilder();
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
