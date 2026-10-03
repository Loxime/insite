<?php

namespace App\Repository;

use App\Entity\CareerOffer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CareerOffer>
 */
final class CareerOfferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CareerOffer::class);
    }

    /**
     * @return list<CareerOffer>
     */
    public function findPublicOffers(): array
    {
        return $this
            ->createQueryBuilder('offer')
            ->andWhere('offer.enabled = true')
            ->orderBy('offer.displayOrder', 'ASC')
            ->addOrderBy('offer.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
