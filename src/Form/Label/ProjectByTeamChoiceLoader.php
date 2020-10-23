<?php

namespace App\Form\Label;

use App\Entity\Label;
use App\Entity\Project;
use App\Entity\Team;
use App\Manager\ProjectManager;
use App\Services\Gitlab\GitlabService;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;

class ProjectByTeamChoiceLoader implements ChoiceLoaderInterface
{
    /**
     * @var Team
     */
    private $team;
    /**
     * @var GitlabService
     */
    private $gitlabService;

    /**
     * Constructor.
     *
     * @param Label $label
     * @param GitlabService $gitlabService
     */
    public function __construct(Label $label, GitlabService $gitlabService)
    {
        $this->team = $label->getTeam();
        $this->gitlabService = $gitlabService;
    }

    public function loadChoiceList(callable $value = null)
    {
        $choices = [];

        foreach ($this->team->getProjects() as $project) {
            $project->setName($this->gitlabService->getProjectById($project)['name']);
            $choices[$project->getExternalId()] = $project;
        }

        return new ArrayChoiceList($choices);
    }

    public function loadChoicesForValues(array $values, callable $value = null)
    {
        $result = [];

        $i = 0;
        foreach ($this->team->getProjects() as $project) {
            if ($i === (int)$values[0]) {
                $project->setName($this->gitlabService->getProjectById($project)['name']);
                $result[] = $project;
            }

            $i++;
        }

        return $result;
    }

    public function loadValuesForChoices(array $choices, callable $value = null)
    {
        $result = [];

        $i = 0;
        if(isset($choices) && !empty($choices[0])){
            foreach ($this->team->getProjects() as $teamProject) {
                if ($choices[0]->getId() === $teamProject->getId()) {
                    $result[] = $i;
                }
                $i++;
            }
        }

        return $result;
    }
}
