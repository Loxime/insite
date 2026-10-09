<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TemporaryAnnouncement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class TemporaryAnnouncementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TemporaryAnnouncement::class);
    }

    public function findActive(
        \DateTimeImmutable $now,
    ): ?TemporaryAnnouncement {
        return $this->createQueryBuilder('announcement')
            ->andWhere('announcement.enabled = :enabled')
            ->andWhere('announcement.imageKey IS NOT NULL')
            ->andWhere(
                '(announcement.startsAt IS NULL OR announcement.startsAt <= :now)'
            )
            ->andWhere(
                '(announcement.endsAt IS NULL OR announcement.endsAt > :now)'
            )
            ->setParameter('enabled', true)
            ->setParameter('now', $now)
            ->orderBy('announcement.createdAt', 'DESC')
            ->addOrderBy('announcement.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
