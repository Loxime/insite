<?php

namespace App\Service;

use App\Entity\Game;
use App\Repository\GameRepository;

final class GameSlugger
{
    public function __construct(
        private readonly GameRepository $gameRepository,
    ) {
    }

    public function generate(
        string $title,
        ?Game $currentGame = null,
    ): string {
        $normalized = iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            $title,
        );

        if ($normalized === false) {
            $normalized = $title;
        }

        $slug = strtolower($normalized);

        $slug = preg_replace(
            '/[^a-z0-9]+/',
            '-',
            $slug,
        ) ?? '';

        $slug = trim($slug, '-');

        if ($slug === '') {
            $slug = 'game';
        }

        $baseSlug = $slug;
        $suffix = 2;

        while (true) {
            $existing = $this->gameRepository->findOneBy([
                'slug' => $slug,
            ]);

            if ($existing === null) {
                return $slug;
            }

            if (
                $currentGame !== null
                && $existing->getId() === $currentGame->getId()
            ) {
                return $slug;
            }

            $slug = $baseSlug . '-' . $suffix;
            ++$suffix;
        }
    }
}
