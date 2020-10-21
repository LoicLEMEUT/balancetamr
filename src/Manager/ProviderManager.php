<?php

namespace App\Manager;

use App\Entity\Provider;
use App\Entity\Team;
use App\Repository\ProviderRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProviderManager
{

    /**
     * @var ProviderRepository
     */
    private $providerRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->providerRepository = $entityManager->getRepository(Provider::class);
        $this->entityManager = $entityManager;
    }

    public function findAll(): array
    {
        return $this->providerRepository->findAll();
    }

    public function findOneById(int $id): ?Provider
    {
        return $this->providerRepository->findOneById($id);
    }

    public function save(Provider $provider): Provider
    {
        $this->entityManager->persist($provider);
        $this->entityManager->flush();

        return $provider;
    }

    public function remove(Provider $provider): void
    {
        $this->entityManager->remove($provider);
        $this->entityManager->flush();
    }

}
