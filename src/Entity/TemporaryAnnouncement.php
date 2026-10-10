<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TemporaryAnnouncementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TemporaryAnnouncementRepository::class)]
#[ORM\Table(name: 'temporary_announcement')]
class TemporaryAnnouncement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public const TYPE_POPUP = 'popup';
    public const TYPE_BANNER = 'banner';

    #[ORM\Column(length: 12, options: ['default' => 'popup'])]
    private string $type = self::TYPE_POPUP;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bannerText = null;

    #[ORM\Column(length: 512, nullable: true)]
    private ?string $imageKey = null;

    #[ORM\Column(length: 120)]
    private string $buttonLabel = '';

    #[ORM\Column(length: 2048)]
    private string $buttonUrl = '';

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startsAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endsAt = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $enabled = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        if (!in_array(
            $type,
            [self::TYPE_POPUP, self::TYPE_BANNER],
            true,
        )) {
            throw new \InvalidArgumentException(
                'Type d’annonce invalide.',
            );
        }

        $this->type = $type;

        return $this;
    }

    public function getBannerText(): ?string
    {
        return $this->bannerText;
    }

    public function setBannerText(?string $text): static
    {
        $text = trim((string) $text);
        $this->bannerText = $text === '' ? null : $text;

        return $this;
    }

    public function getImageKey(): ?string
    {
        return $this->imageKey;
    }

    public function setImageKey(?string $imageKey): static
    {
        $this->imageKey = $imageKey;
        return $this;
    }

    public function getButtonLabel(): string
    {
        return $this->buttonLabel;
    }

    public function setButtonLabel(string $buttonLabel): static
    {
        $this->buttonLabel = $buttonLabel;
        return $this;
    }

    public function getButtonUrl(): string
    {
        return $this->buttonUrl;
    }

    public function setButtonUrl(string $buttonUrl): static
    {
        $this->buttonUrl = $buttonUrl;
        return $this;
    }

    public function getStartsAt(): ?\DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function setStartsAt(?\DateTimeImmutable $startsAt): static
    {
        $this->startsAt = $startsAt;
        return $this;
    }

    public function getEndsAt(): ?\DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function setEndsAt(?\DateTimeImmutable $endsAt): static
    {
        $this->endsAt = $endsAt;
        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function touchUpdatedAt(): static
    {
        $this->updatedAt = new \DateTimeImmutable(
            'now',
            new \DateTimeZone('UTC'),
        );

        return $this;
    }
}
