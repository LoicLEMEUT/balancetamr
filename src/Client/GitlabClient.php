<?php

namespace App\Client;

use App\Entity\Provider;
use App\Manager\ProviderManager;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GitlabClient
{

    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * GitlabService constructor.
     * @param HttpClientInterface $client
     */
    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getProjects(Provider $provider)
    {
        $projects = [];

        // Variants.
        $page = 1;
        do {
            $projectsCurrent = $this->client->request(
                'GET',
                $provider->getUrl() . '/api/v4/projects',
                [
                    'query' => [
                        'archived' => false,
                        'private_token' => $provider->getToken(),
                        'page' => $page,
                        'per_page' => 100,
                    ]
                ]
            )->toArray();

            $projects = array_merge($projects, $projectsCurrent);
            $page++;
        } while (!empty($projectsCurrent));

        return $projects;
    }

    public function getProjectById(Provider $provider, int $id)
    {
        return $this->client->request(
            'GET',
            $provider->getUrl() . '/api/v4/projects/' . $id,
            [
                'query' => [
                    'private_token' => $provider->getToken(),
                ]
            ]
        )->toArray();
    }

    public function getMrsByProject(Provider $provider, int $id, ?array $labels = [])
    {
        $mrs = [];

        $page = 1;
        do {
            $mrsCurrent = $this->client->request(
                'GET',
                $provider->getUrl() . '/api/v4/projects/' . $id . '/merge_requests',
                [
                    'query' => [
                        'private_token' => $provider->getToken(),
                        'scope' => 'all',
                        'state' => 'opened',
                        'per_page' => 100,
                        'page' => $page,
                        'wip' => 'no',
                        'with_labels_details' => true,
                    ]
                ]
            )->toArray();

            // Filter Labels request in mode OR (Gitlab apply only mode AND).
            if (!empty($labels)) {
                foreach ($mrsCurrent as $key => $mr) {
                    if (!empty($mr['labels'])) {
                        $hasLabel = false;
                        foreach ($mr['labels'] as $mrLabel) {
                            if (in_array($mrLabel['id'], $labels, true)) {
                                $hasLabel = true;
                                break;
                            }
                        }
                        if (!$hasLabel) {
                            unset($mrsCurrent[$key]);
                        }
                    }
                }
            }

            $mrs = array_merge($mrs, $mrsCurrent);
            $page++;
        } while (!empty($mrsCurrent));

        return $mrs;
    }

    public function getLabelsByProject(Provider $provider, int $id)
    {
        return $this->client->request(
            'GET',
            $provider->getUrl() . '/api/v4/projects/' . $id . '/labels',
            [
                'query' => [
                    'private_token' => $provider->getToken(),
                ]
            ]
        )->toArray();
    }

    public function getLabelByProject(Provider $provider, int $id, int $idLabel)
    {
        return $this->client->request(
            'GET',
            $provider->getUrl() . '/api/v4/projects/' . $id . '/labels/' . $idLabel,
            [
                'query' => [
                    'private_token' => $provider->getToken(),
                ]
            ]
        )->toArray();
    }
}
