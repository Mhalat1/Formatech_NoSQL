<?php

namespace App\Repository;

use App\Entity\UtilisateurInstitutionSessionModule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class UtilisateurInstitutionSessionModuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UtilisateurInstitutionSessionModule::class);
    }
public function comptageUtilisateurParSessionModule(): array
{
    return $this->createQueryBuilder('uism')
        ->select('COUNT(uism.id) as total')
        ->getQuery()
        ->getResult();
}
}
