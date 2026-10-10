<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'jam_challenge')]
class JamChallenge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 160)]
    private string $name = '';

    #[ORM\Column(length: 180, unique: true)]
    private string $slug = '';

    #[ORM\Column(length: 255)]
    private string $theme = '';

    /** @var list<string> */
    #[ORM\Column(type: Types::JSON)]
    private array $colors = ['#F4B942'];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $startsAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $endsAt = null;

    /** @var list<array{name:string,url:string,icon:string}> */
    #[ORM\Column(type: Types::JSON)]
    private array $platforms = [
        ['name' => 'Steam', 'url' => 'https://partner.steamgames.com/', 'icon' => 'steam'],
        ['name' => 'itch.io', 'url' => 'https://itch.io/game/new', 'icon' => 'itch-io'],
    ];

    #[ORM\Column(options: ['default' => false])]
    private bool $enabled = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = trim($name); return $this; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }
    public function getTheme(): string { return $this->theme; }
    public function setTheme(string $theme): static { $this->theme = trim($theme); return $this; }
    /** @return list<string> */
    public function getColors(): array { return $this->colors; }
    /** @param list<string> $colors */
    public function setColors(array $colors): static { $this->colors = array_values($colors); return $this; }
    public function getStartsAt(): ?\DateTimeImmutable { return $this->startsAt; }
    public function setStartsAt(?\DateTimeImmutable $startsAt): static { $this->startsAt = $startsAt; return $this; }
    public function getEndsAt(): ?\DateTimeImmutable { return $this->endsAt; }
    public function setEndsAt(?\DateTimeImmutable $endsAt): static { $this->endsAt = $endsAt; return $this; }
    /** @return list<array{name:string,url:string,icon:string}> */
    public function getPlatforms(): array { return $this->platforms; }
    /** @param list<array{name:string,url:string,icon:string}> $platforms */
    public function setPlatforms(array $platforms): static { $this->platforms = array_values($platforms); return $this; }
    public function isEnabled(): bool { return $this->enabled; }
    public function setEnabled(bool $enabled): static { $this->enabled = $enabled; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function getDurationHours(): int
    {
        if ($this->startsAt === null || $this->endsAt === null) {
            return 0;
        }

        return (int) ceil(max(0, $this->endsAt->getTimestamp() - $this->startsAt->getTimestamp()) / 3600);
    }

    public function getStateAt(\DateTimeImmutable $now): string
    {
        if (!$this->enabled || $this->startsAt === null || $this->endsAt === null || $this->startsAt >= $this->endsAt) {
            return 'disabled';
        }

        if ($now < $this->startsAt) {
            return 'upcoming';
        }

        if ($now < $this->endsAt) {
            return 'active';
        }

        if ($now < $this->endsAt->modify('+3 days')) {
            return 'submissions';
        }

        return 'expired';
    }
}
