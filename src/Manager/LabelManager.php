<?php

namespace App\Manager;

use App\Entity\Label;
use App\Entity\Team;
use App\Repository\LabelRepository;
use Doctrine\ORM\EntityManagerInterface;

class LabelManager
{

    /**
     * @var LabelRepository
     */
    private $labelRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->labelRepository = $entityManager->getRepository(Label::class);
        $this->entityManager = $entityManager;
    }

    public function findByTeam(Team $team): ?Label
    {
        return $this->labelRepository->findOneByTeam($team);
    }

    public function findById(int $id): ?Label
    {
        return $this->labelRepository->findOneById($id);
    }

    public function save(Label $label): Label
    {
        $this->entityManager->persist($label);
        $this->entityManager->flush();

        return $label;
    }

    public function remove(Label $label): void
    {
        $this->entityManager->remove($label);
        $this->entityManager->flush();
    }

}
