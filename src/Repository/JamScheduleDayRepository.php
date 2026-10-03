<?php

namespace App\Repository;

use App\Entity\Jam;
use App\Entity\JamScheduleDay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;

final class JamScheduleDayRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct(
            $registry,
            JamScheduleDay::class,
        );
    }

    public function findFor(
        Jam $jam,
        \DateTimeImmutable $date,
    ): ?JamScheduleDay {
        return $this
            ->createQueryBuilder('day')
            ->andWhere('day.jam = :jam')
            ->andWhere('day.date = :date')
            ->setParameter('jam', $jam)
            ->setParameter(
                'date',
                $date,
                Types::DATE_IMMUTABLE,
            )
            ->getQuery()
            ->getOneOrNullResult();
    }
}
