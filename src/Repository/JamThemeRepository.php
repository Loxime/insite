<?php

namespace App\Repository;

use App\Entity\JamTheme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JamTheme>
 */
final class JamThemeRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct(
            $registry,
            JamTheme::class,
        );
    }

    /**
     * @return list<JamTheme>
     */
    public function findEnabledOrdered(): array
    {
        return $this->findBy(
            ['enabled' => true],
            ['name' => 'ASC'],
        );
    }
}
