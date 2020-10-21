<?php

namespace App\Manager;

use App\Entity\Team;
use App\Repository\TeamRepository;
use App\Services\Gitlab\GitlabService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Gitlab\Client;

class TeamManager
{
    /**
     * @var TeamRepository
     */
    private $teamRepository;
    /**
     * @var GitlabService
     */
    private $gitlabService;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, GitlabService $gitlabService)
    {
        $this->teamRepository = $entityManager->getRepository(Team::class);
        $this->gitlabService = $gitlabService;
        $this->entityManager = $entityManager;
    }

    public function getAllTeams(){
        return $this->teamRepository->findAll();
    }

    public function save(Team $team): Team
    {
        $this->entityManager->persist($team);
        $this->entityManager->flush();

        return $team;
    }

    public function remove(Team $team): void
    {
        $this->entityManager->remove($team);
        $this->entityManager->flush();
    }

    public function getMrsByTeam(Team $team): array
    {
        $listOfLabels = [];
        $listOfMrs = [];

        foreach ($team->getLabels() as $label){
            $listOfLabels[$label->getProject()->getExternalId()] = $label->getCodes();
        }

        foreach ($team->getProjects() as $project){
            $mrs = $this->gitlabService->getMrsByProject(
                $project->getProvider(),
                $project->getExternalId(),
                ($listOfLabels[$project->getExternalId()] ?? [])
            );
            foreach ($mrs as $projectMr){
                $listOfMrs[] = $projectMr;
            }
        }

        return $listOfMrs;
    }

    public function getProjectsByTeam(Team $team): array
    {
        $listOfProject = [];
        foreach ($team->getProjects() as $project){
            $gitlabProject = $this->gitlabService->getProjectById($project->getProvider(), $project->getExternalId());
            $listOfProject[$project->getExternalId()] = $gitlabProject;
        }
        return $listOfProject;
    }

    public function getLabelsByTeam(Team $team): array
    {
        $listOfLabels = [];
        foreach ($team->getProjects() as $project){
            foreach ($this->gitlabService->getLabelsByProject($project->getExternalId(), $project->getProvider()) as $label){
                $listOfLabels[$label['name']] = $label;
            }
        }
        return $listOfLabels;
    }
}
