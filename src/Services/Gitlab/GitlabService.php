<?php

namespace App\Services\Gitlab;

use App\Client\GitlabClient;
use App\Entity\Label;
use App\Entity\Provider;
use App\Manager\ProviderManager;

class GitlabService
{
    /**
     * @var array
     */
    protected $providers = [];
    /**
     * @var ProviderManager
     */
    private $providerManager;

    /**
     * @var GitlabClient
     */
    private $gitlabClient;

    /**
     * GitlabService constructor.
     * @param ProviderManager $providerManager
     * @param GitlabClient $gitlabClient
     */
    public function __construct(ProviderManager $providerManager, GitlabClient $gitlabClient)
    {
        $this->providerManager = $providerManager;
        $this->gitlabClient = $gitlabClient;
    }

    public function getProjects(?Provider $provider = null)
    {
        $projects = [];

        if ($provider !== null) {
            $projects[$provider->getId()] = $this->gitlabClient->getProjects($provider);
        } else {
            foreach ($this->providerManager->findAll() as $providerList) {
                $projects[$providerList->getId()] = $this->gitlabClient->getProjects($providerList);
            }
        }

        return $projects;
    }

    public function getProjectById(Provider $provider, int $id)
    {
        return $this->gitlabClient->getProjectById($provider, $id);
    }

    public function getMrsByProject(Provider $provider, int $id, ?Label $label = null)
    {
        return $this->gitlabClient->getMrsByProject($provider, $id, $label);
    }

    public function getLabelsByProject(Provider $provider, int $id)
    {
        return $this->gitlabClient->getLabelsByProject($provider, $id);
    }

    public function getLabelByProject(Provider $provider, int $id, int $idLabel)
    {
        return $this->gitlabClient->getLabelByProject($provider, $id, $idLabel);
    }
}
