<?php

namespace App\Repository;

use App\Entity\Jam;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;

final class JamRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct(
            $registry,
            Jam::class,
        );
    }

    public function findActiveSpecial(
        \DateTimeImmutable $date,
    ): ?Jam {
        return $this
            ->createQueryBuilder('jam')
            ->andWhere('jam.enabled = true')
            ->andWhere('jam.permanent = false')
            ->andWhere('jam.startDate <= :date')
            ->andWhere('jam.endDate >= :date')
            ->setParameter(
                'date',
                $date,
                Types::DATE_IMMUTABLE,
            )
            ->orderBy('jam.startDate', 'DESC')
            ->addOrderBy('jam.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPermanent(): ?Jam
    {
        return $this
            ->createQueryBuilder('jam')
            ->andWhere('jam.enabled = true')
            ->andWhere('jam.permanent = true')
            ->orderBy('jam.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOtherPermanent(
        ?int $excludeId,
    ): ?Jam {
        $qb = $this
            ->createQueryBuilder('jam')
            ->andWhere('jam.permanent = true');

        if ($excludeId !== null) {
            $qb
                ->andWhere('jam.id != :id')
                ->setParameter('id', $excludeId);
        }

        return $qb
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOverlappingSpecial(
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
        ?int $excludeId,
    ): ?Jam {
        $qb = $this
            ->createQueryBuilder('jam')
            ->andWhere('jam.enabled = true')
            ->andWhere('jam.permanent = false')
            ->andWhere('jam.startDate <= :end')
            ->andWhere('jam.endDate >= :start')
            ->setParameter(
                'start',
                $start,
                Types::DATE_IMMUTABLE,
            )
            ->setParameter(
                'end',
                $end,
                Types::DATE_IMMUTABLE,
            );

        if ($excludeId !== null) {
            $qb
                ->andWhere('jam.id != :id')
                ->setParameter('id', $excludeId);
        }

        return $qb
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
