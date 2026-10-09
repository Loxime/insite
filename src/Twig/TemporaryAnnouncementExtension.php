<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\TemporaryAnnouncement;
use App\Repository\TemporaryAnnouncementRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class TemporaryAnnouncementExtension extends AbstractExtension
{
    public function __construct(
        private readonly TemporaryAnnouncementRepository $repository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'active_temporary_announcement',
                [$this, 'getActiveAnnouncement'],
            ),
        ];
    }

    public function getActiveAnnouncement(): ?TemporaryAnnouncement
    {
        return $this->repository->findActive(
            new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
        );
    }
}
