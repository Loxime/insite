<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\JamChallenge;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class JamChallengeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JamChallenge::class);
    }

    public function findActive(): ?JamChallenge
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        return $this->createQueryBuilder('jam')
            ->andWhere('jam.enabled = :enabled')
            ->andWhere('jam.startsAt <= :now')
            ->andWhere('jam.endsAt > :now')
            ->setParameter('enabled', true)
            ->setParameter('now', $now)
            ->orderBy('jam.startsAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
