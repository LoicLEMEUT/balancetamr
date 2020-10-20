<?php

namespace App\Services\Gitlab;

use Exception;

class GitlabService
{
    /**
     * @var array
     */
    protected $providers = [];

    public function addProvider(GitlabInterface $provider, $alias)
    {
        $this->providers[$alias] = $provider;
    }

    public function getProvider($alias): GitlabInterface
    {
        if (array_key_exists($alias, $this->providers)) {
            return $this->providers[$alias];
        }

        throw new Exception("Provider '$alias' doesn't exist");
    }

    public function getProjects(?string $provider = null)
    {
        $projects = [];

        if (!empty($provider)) {
            $projects[$provider] = $this->getProvider($provider)->getProjects();
        } else {
            foreach ($this->providers as $alias => $providerClass) {
                $projects[$alias] = $providerClass->getProjects();
            }
        }

        return $projects;
    }

    public function getProjectById(int $id, string $provider)
    {
        return $this->getProvider($provider)->getProjectById($id);
    }

    public function getMrsByProject(int $id, string $provider, ?array $labels = [])
    {
        return $this->getProvider($provider)->getMrsByProject($id, $labels);
    }

    public function getLabelsByProject(int $id, string $provider)
    {
        return $this->getProvider($provider)->getLabelsByProject($id);
    }

    public function getLabelByProject(int $id, string $provider, int $idLabel)
    {
        return $this->getProvider($provider)->getLabelByProject($id, $idLabel);
    }
}
