<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SiteSocialLink
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'socialLinks')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?SiteSettings $siteSettings = null;

    #[ORM\Column(length: 50)]
    private ?string $platform = null;

    #[ORM\Column(length: 2048)]
    private ?string $url = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $enabled = true;

    #[ORM\Column(options: ['default' => 0])]
    private int $displayOrder = 0;

    public function getId(): ?int { return $this->id; }

    public function getSiteSettings(): ?SiteSettings
    {
        return $this->siteSettings;
    }

    public function setSiteSettings(?SiteSettings $siteSettings): static
    {
        $this->siteSettings = $siteSettings;
        return $this;
    }

    public function getPlatform(): ?string { return $this->platform; }

    public function setPlatform(string $platform): static
    {
        $this->platform = $platform;
        return $this;
    }

    public function getUrl(): ?string { return $this->url; }

    public function setUrl(string $url): static
    {
        $this->url = $url;
        return $this;
    }

    public function isEnabled(): bool { return $this->enabled; }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function getDisplayOrder(): int { return $this->displayOrder; }

    public function setDisplayOrder(int $displayOrder): static
    {
        $this->displayOrder = $displayOrder;
        return $this;
    }
}
