<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function createAdminListQuery(?string $search = null): QueryBuilder
    {
        $queryBuilder = $this
            ->createQueryBuilder('post')
            ->orderBy('post.createdAt', 'DESC');

        $search = trim((string) $search);

        if ($search !== '') {
            $queryBuilder
                ->andWhere('LOWER(post.title) LIKE LOWER(:search)')
                ->setParameter('search', '%' . $search . '%');
        }

        return $queryBuilder;
    }

    /**
     * @return list<Post>
     */
    public function findLatestPublished(int $limit = 5): array
    {
        return $this
            ->createQueryBuilder('post')
            ->andWhere('post.status = :status')
            ->setParameter('status', 'published')
            ->orderBy('post.createdAt', 'DESC')
            ->addOrderBy('post.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }


    /**
     * @return list<Post>
     */
    public function findPublishedPage(
        int $page,
        int $limit = 5,
    ): array {
        $page = max(1, $page);
        $limit = max(1, $limit);

        return $this
            ->createQueryBuilder('post')
            ->andWhere('post.status = :status')
            ->setParameter('status', 'published')
            ->orderBy('post.createdAt', 'DESC')
            ->addOrderBy('post.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countPublished(): int
    {
        return (int) $this
            ->createQueryBuilder('post')
            ->select('COUNT(post.id)')
            ->andWhere('post.status = :status')
            ->setParameter('status', 'published')
            ->getQuery()
            ->getSingleScalarResult();
    }


}
