<?php

namespace App\Repository;

use App\Entity\FaqQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FaqQuestion>
 */
final class FaqQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FaqQuestion::class);
    }

    /**
     * @return list<FaqQuestion>
     */
    public function findPublicQuestions(): array
    {
        return $this
            ->createQueryBuilder('faq')
            ->andWhere('faq.enabled = true')
            ->orderBy('faq.displayOrder', 'ASC')
            ->addOrderBy('faq.id', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }
}
