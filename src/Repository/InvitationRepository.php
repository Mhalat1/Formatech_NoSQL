<?php

namespace App\Repository;

use App\Entity\Invitation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InvitationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invitation::class);
    }
public function findValidInvitation(string $token): ?Invitation
{
    return $this->createQueryBuilder('i')
        ->where('i.token = :token')
        ->andWhere('i.Expire_Le > :now')
        ->setParameter('token', $token)
        ->setParameter('now', new \DateTimeImmutable())
        ->getQuery()
        ->getOneOrNullResult();
}
}
