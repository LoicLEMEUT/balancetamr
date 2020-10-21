<?php

namespace App\Manager;

use App\Entity\Project;
use App\Entity\Provider;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProjectManager
{

    /**
     * @var ProjectRepository
     */
    private $projectRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->projectRepository = $entityManager->getRepository(Project::class);
        $this->entityManager = $entityManager;
    }

    public function findByExternalId(int $externalId, Provider $provider): ?Project
    {
        return $this->projectRepository->findOneBy(['externalId' => $externalId, 'provider' => $provider]);
    }

    public function create(Project $project): Project
    {
        $this->entityManager->persist($project);
        $this->entityManager->flush();

        return $project;
    }

}
