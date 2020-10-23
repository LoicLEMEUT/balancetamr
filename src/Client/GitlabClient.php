<?php

namespace App\Client;

use App\Entity\Label;
use App\Entity\Provider;
use App\Manager\ProviderManager;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GitlabClient
{

    public const DEFAULT_PAGE = 1;
    public const DEFAULT_PER_PAGE = 100;

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
        $page = self::DEFAULT_PAGE;
        do {
            $projectsCurrent = $this->client->request(
                'GET',
                $provider->getUrl() . '/api/v4/projects',
                [
                    'query' => [
                        'archived' => false,
                        'private_token' => $provider->getToken(),
                        'page' => $page,
                        'per_page' => self::DEFAULT_PER_PAGE,
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

    /**
     * @param Provider $provider
     * @param int $id
     * @param Label|null $label
     * @return array
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getMrsByProject(Provider $provider, int $id, ?Label $label = null)
    {
        $mrs = [];

        $page = self::DEFAULT_PAGE;
        do {
            $mrsCurrent = $this->client->request(
                'GET',
                $provider->getUrl() . '/api/v4/projects/' . $id . '/merge_requests',
                [
                    'query' => [
                        'private_token' => $provider->getToken(),
                        'scope' => 'all',
                        'state' => 'opened',
                        'per_page' => self::DEFAULT_PER_PAGE,
                        'page' => $page,
                        'wip' => 'no',
                        'with_labels_details' => true,
                    ]
                ]
            )->toArray();

            // Filter Labels request in mode OR (Gitlab apply only mode AND).
            if ($label !== null) {
                foreach ($mrsCurrent as $key => $mr) {
                    if (!empty($mr['labels'])) {
                        $hasLabel = false;
                        foreach ($mr['labels'] as $mrLabel) {
                            if (in_array($mrLabel['id'], $label->getCodes(), true)) {
                                $hasLabel = true;
                                break;
                            }
                        }

                        // If inclusion is TRUE.
                        if ($label->getInclusion() === true) {
                            // If we don't found label, remove it.
                            if (!$hasLabel) {
                                unset($mrsCurrent[$key]);
                            }
                        } else {
                            // If we found Label, remove it.
                            if ($hasLabel) {
                                unset($mrsCurrent[$key]);
                            }
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
