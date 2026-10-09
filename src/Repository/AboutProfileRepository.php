<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AboutProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class AboutProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AboutProfile::class);
    }

    /**
     * @return list<AboutProfile>
     */
    public function findVisibleOrdered(): array
    {
        return $this->createQueryBuilder('profile')
            ->andWhere('profile.enabled = :enabled')
            ->setParameter('enabled', true)
            ->orderBy('profile.displayOrder', 'ASC')
            ->addOrderBy('profile.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findVisibleBySlug(
        string $slug,
    ): ?AboutProfile {
        return $this->findOneBy([
            'slug' => $slug,
            'enabled' => true,
        ]);
    }
}
