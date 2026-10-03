<?php

namespace App\Repository;

use App\Entity\About;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<About>
 */
final class AboutRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, About::class);
    }

    public function findContent(): ?About
    {
        return $this
            ->createQueryBuilder('about')
            ->leftJoin(
                'about.socialLinks',
                'socialLink',
            )
            ->addSelect('socialLink')
            ->orderBy(
                'socialLink.displayOrder',
                'ASC',
            )
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
