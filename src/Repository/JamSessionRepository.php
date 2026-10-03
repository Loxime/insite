<?php

namespace App\Repository;

use App\Entity\Jam;
use App\Entity\JamSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;

final class JamSessionRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct(
            $registry,
            JamSession::class,
        );
    }

    public function findFor(
        Jam $jam,
        \DateTimeImmutable $date,
    ): ?JamSession {
        return $this
            ->createQueryBuilder('session')
            ->andWhere('session.jam = :jam')
            ->andWhere('session.date = :date')
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
