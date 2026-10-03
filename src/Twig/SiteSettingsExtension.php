<?php

namespace App\Twig;

use App\Entity\SiteSettings;
use App\Repository\SiteSettingsRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class SiteSettingsExtension extends AbstractExtension
{
    private bool $loaded = false;

    private ?SiteSettings $settings = null;

    public function __construct(
        private readonly SiteSettingsRepository $repository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'site_settings',
                [$this, 'getSettings'],
            ),
        ];
    }

    public function getSettings(): ?SiteSettings
    {
        if (!$this->loaded) {
            $this->settings = $this->repository->findSettings();
            $this->loaded = true;
        }

        return $this->settings;
    }
}
