<?php

namespace App\Repository;

use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Game>
 */
final class GameRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, Game::class);
    }

    public function createAdminListQuery(
        ?string $search = null,
    ): QueryBuilder {
        $qb = $this
            ->createQueryBuilder('game')
            ->orderBy('game.displayOrder', 'ASC')
            ->addOrderBy('game.createdAt', 'DESC');

        $search = trim((string) $search);

        if ($search !== '') {
            $qb
                ->andWhere(
                    'LOWER(game.title) LIKE LOWER(:search)',
                )
                ->setParameter(
                    'search',
                    '%' . $search . '%',
                );
        }

        return $qb;
    }

    /**
     * @return list<Game>
     */
    public function findPublishedOrdered(): array
    {
        return $this
            ->createQueryBuilder('game')
            ->andWhere('game.status = :status')
            ->setParameter('status', 'published')
            ->orderBy('game.displayOrder', 'ASC')
            ->addOrderBy('game.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
