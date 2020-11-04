<?php

namespace App\Form\Team;

use App\Entity\Project;
use App\Manager\ProjectManager;
use App\Manager\ProviderManager;
use App\Services\Gitlab\GitlabService;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;

class ProjectTypeChoiceLoader implements ChoiceLoaderInterface
{
    /** @var ArrayChoiceList */
    protected $choices;

    /**
     * @var GitlabService
     */
    private $gitlabService;
    /**
     * @var ProjectManager
     */
    private $projectManager;
    /**
     * @var ProviderManager
     */
    private $providerManager;

    /**
     * Constructor.
     *
     * @param GitlabService $gitlabService
     * @param ProjectManager $projectManager
     * @param ProviderManager $providerManager
     */
    public function __construct(GitlabService $gitlabService, ProjectManager $projectManager, ProviderManager $providerManager)
    {
        $this->gitlabService = $gitlabService;
        $this->projectManager = $projectManager;
        $this->providerManager = $providerManager;
    }

    public function loadChoiceList(callable $value = null)
    {
        $choices = [];
        foreach ($this->gitlabService->getProjects() as $providerId => $gitlabProviders) {
            foreach ($gitlabProviders as $gitlabProject) {
                $provider = $this->providerManager->findOneById($providerId);

                $project = new Project();
                $project->setExternalId($gitlabProject['id']);
                $project->setName($gitlabProject['name']);
                $project->setProvider($provider);

                $choices[$project->getExternalId() . $project->getProvider()->getId()] = $project;
            }
        }

        $this->choices = new ArrayChoiceList($choices);
        return $this->choices;
    }

    public function loadChoicesForValues(array $values, callable $value = null)
    {
        $result = [];

        $choices = $this->choices->getChoices();
        foreach ($values as $id) {
            if (isset($choices[$id])) {
                $project = $this->projectManager->findByExternalId($choices[$id]->getExternalId(), $choices[$id]->getProvider());
                if ($project === null) {
                    $project = $this->projectManager->create($choices[$id]);
                }

                $result[] = $project;
            }
        }

        return $result;
    }

    public function loadValuesForChoices(array $choices, callable $value = null)
    {
        $result = [];

        /** @var Project $project */
        foreach ($choices as $project) {
            if(isset($this->choices->getStructuredValues()[$project->getExternalId() . $project->getProvider()->getId()])){
                $result[] = $this->choices->getStructuredValues()[$project->getExternalId() . $project->getProvider()->getId()];
            }
        }

        return $result;
    }
}
