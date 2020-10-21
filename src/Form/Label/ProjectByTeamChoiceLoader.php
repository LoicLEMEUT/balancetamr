<?php

namespace App\Form\Label;

use App\Entity\Label;
use App\Entity\Team;
use App\Services\Gitlab\GitlabService;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;

class ProjectByTeamChoiceLoader implements ChoiceLoaderInterface
{
    /** @var array */
    protected $choices;

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
            $project->setName($this->gitlabService->getProjectById($project->getProvider(), $project->getExternalId())['name']);
            $choices[$project->getExternalId()] = $project;
        }

        return new ArrayChoiceList($choices);
    }

    public function loadChoicesForValues(array $values, callable $value = null)
    {
        $result = [ ];

        $i = 0;
        foreach ($this->team->getProjects() as $project) {
            if($i === (int) $values[0]){
                $project->setName($this->gitlabService->getProjectById($project->getProvider(), $project->getExternalId())['name']);
                $result[] = $project;
            }

            $i++;
        }

        return $result;
    }

    public function loadValuesForChoices(array $choices, callable $value = null)
    {
        return [];
    }
}
