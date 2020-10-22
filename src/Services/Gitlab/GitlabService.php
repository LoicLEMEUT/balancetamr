<?php

namespace App\Services\Gitlab;

use App\Client\GitlabClient;
use App\Entity\Label;
use App\Entity\Project;
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

    public function getProjectById(Project $project)
    {
        return $this->gitlabClient->getProjectById($project->getProvider(), $project->getExternalId());
    }

    public function getMrsByProject(Project $project, ?Label $label = null)
    {
        return $this->gitlabClient->getMrsByProject($project->getProvider(), $project->getExternalId(), $label);
    }

    public function getLabelsByProject(Project $project)
    {
        return $this->gitlabClient->getLabelsByProject($project->getProvider(), $project->getExternalId());
    }

    public function getLabelByProject(Project $project, int $idLabel)
    {
        return $this->gitlabClient->getLabelByProject($project->getProvider(), $project->getExternalId(), $idLabel);
    }
}
