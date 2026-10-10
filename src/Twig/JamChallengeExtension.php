<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\JamChallenge;
use App\Repository\JamChallengeRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class JamChallengeExtension extends AbstractExtension
{
    public function __construct(
        private readonly JamChallengeRepository $jamChallengeRepository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'active_jam_challenge',
                [$this, 'getActiveJamChallenge'],
            ),
        ];
    }

    public function getActiveJamChallenge(): ?JamChallenge
    {
        return $this->jamChallengeRepository->findActive();
    }
}
