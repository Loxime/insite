<?php

namespace App\Twig;

use App\Jam\JamResolver;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class JamExtension extends AbstractExtension
{
    public function __construct(
        private readonly JamResolver $resolver,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'daily_jam',
                $this->resolver->current(...),
            ),
        ];
    }
}
