<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ChangelogEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class ChangelogEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChangelogEntry::class);
    }

    /**
     * @return list<ChangelogEntry>
     */
    public function findPublishedOrdered(): array
    {
        return $this->createQueryBuilder('entry')
            ->andWhere('entry.publishedAt IS NOT NULL')
            ->orderBy('entry.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findLatestPublished(): ?ChangelogEntry
    {
        return $this->createQueryBuilder('entry')
            ->andWhere('entry.publishedAt IS NOT NULL')
            ->orderBy('entry.publishedAt', 'DESC')
            ->addOrderBy('entry.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOlderPublishedThan(
        ChangelogEntry $current,
    ): ?ChangelogEntry {
        return $this->createQueryBuilder('entry')
            ->andWhere('entry.publishedAt IS NOT NULL')
            ->andWhere(
                '(entry.publishedAt < :publishedAt OR
                (entry.publishedAt = :publishedAt AND entry.id < :id))'
            )
            ->setParameter('publishedAt', $current->getPublishedAt())
            ->setParameter('id', $current->getId())
            ->orderBy('entry.publishedAt', 'DESC')
            ->addOrderBy('entry.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findNewerPublishedThan(
        ChangelogEntry $current,
    ): ?ChangelogEntry {
        return $this->createQueryBuilder('entry')
            ->andWhere('entry.publishedAt IS NOT NULL')
            ->andWhere(
                '(entry.publishedAt > :publishedAt OR
                (entry.publishedAt = :publishedAt AND entry.id > :id))'
            )
            ->setParameter('publishedAt', $current->getPublishedAt())
            ->setParameter('id', $current->getId())
            ->orderBy('entry.publishedAt', 'ASC')
            ->addOrderBy('entry.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPublishedByVersion(string $version): ?ChangelogEntry
    {
        return $this->createQueryBuilder('entry')
            ->andWhere('entry.version = :version')
            ->andWhere('entry.publishedAt IS NOT NULL')
            ->setParameter('version', $version)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
