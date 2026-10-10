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
        return $this->findActiveByType(
            $now,
            TemporaryAnnouncement::TYPE_POPUP,
        );
    }

    public function findActiveBanner(
        \DateTimeImmutable $now,
    ): ?TemporaryAnnouncement {
        return $this->findActiveByType(
            $now,
            TemporaryAnnouncement::TYPE_BANNER,
        );
    }

    private function findActiveByType(
        \DateTimeImmutable $now,
        string $type,
    ): ?TemporaryAnnouncement {
        $requiredContent = $type === TemporaryAnnouncement::TYPE_POPUP
            ? 'announcement.imageKey IS NOT NULL'
            : 'announcement.bannerText IS NOT NULL';

        return $this->createQueryBuilder('announcement')
            ->andWhere('announcement.type = :type')
            ->andWhere('announcement.enabled = :enabled')
            ->andWhere($requiredContent)
            ->andWhere(
                '(announcement.startsAt IS NULL OR announcement.startsAt <= :now)'
            )
            ->andWhere(
                '(announcement.endsAt IS NULL OR announcement.endsAt > :now)'
            )
            ->setParameter('type', $type)
            ->setParameter('enabled', true)
            ->setParameter('now', $now)
            ->orderBy('announcement.createdAt', 'DESC')
            ->addOrderBy('announcement.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
