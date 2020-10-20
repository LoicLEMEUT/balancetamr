<?php

namespace App\Services\Gitlab\Providers;

use App\Services\Gitlab\AbstractGitlabProvider;
use Gitlab\Client;

class HubsideGitlabProvider extends AbstractGitlabProvider
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }
}
