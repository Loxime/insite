<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\SiteSettingsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SiteSettingsRepository::class)]
class SiteSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $brandText = 'INQUEST';

    #[ORM\Column(length: 100)]
    private string $blogLabel = 'Blog';

    #[ORM\Column(options: ['default' => true])]
    private bool $blogEnabled = true;

    #[ORM\Column(length: 100)]
    private string $gamesLabel = 'Games';

    #[ORM\Column(options: ['default' => true])]
    private bool $gamesEnabled = true;

    #[ORM\Column(length: 100)]
    private string $aboutLabel = 'About';

    #[ORM\Column(options: ['default' => true])]
    private bool $aboutEnabled = true;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $footerText = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, SiteSocialLink>
     */
    #[ORM\OneToMany(
        mappedBy: 'siteSettings',
        targetEntity: SiteSocialLink::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    #[ORM\OrderBy(['displayOrder' => 'ASC', 'id' => 'ASC'])]
    private Collection $socialLinks;


    public function __construct()
    {
        $this->socialLinks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrandText(): string
    {
        return $this->brandText;
    }

    public function setBrandText(string $brandText): static
    {
        $this->brandText = $brandText;

        return $this;
    }

    public function getBlogLabel(): string
    {
        return $this->blogLabel;
    }

    public function setBlogLabel(string $blogLabel): static
    {
        $this->blogLabel = $blogLabel;

        return $this;
    }

    public function isBlogEnabled(): bool
    {
        return $this->blogEnabled;
    }

    public function setBlogEnabled(bool $blogEnabled): static
    {
        $this->blogEnabled = $blogEnabled;

        return $this;
    }

    public function getGamesLabel(): string
    {
        return $this->gamesLabel;
    }

    public function setGamesLabel(string $gamesLabel): static
    {
        $this->gamesLabel = $gamesLabel;

        return $this;
    }

    public function isGamesEnabled(): bool
    {
        return $this->gamesEnabled;
    }

    public function setGamesEnabled(bool $gamesEnabled): static
    {
        $this->gamesEnabled = $gamesEnabled;

        return $this;
    }

    public function getAboutLabel(): string
    {
        return $this->aboutLabel;
    }

    public function setAboutLabel(string $aboutLabel): static
    {
        $this->aboutLabel = $aboutLabel;

        return $this;
    }

    public function isAboutEnabled(): bool
    {
        return $this->aboutEnabled;
    }

    public function setAboutEnabled(bool $aboutEnabled): static
    {
        $this->aboutEnabled = $aboutEnabled;

        return $this;
    }

    public function getFooterText(): ?string
    {
        return $this->footerText;
    }

    public function setFooterText(?string $footerText): static
    {
        $this->footerText = $footerText;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
    /**
     * @return Collection<int, SiteSocialLink>
     */
    public function getSocialLinks(): Collection
    {
        return $this->socialLinks;
    }

    public function addSocialLink(SiteSocialLink $link): static
    {
        if (!$this->socialLinks->contains($link)) {
            $this->socialLinks->add($link);
            $link->setSiteSettings($this);
        }

        return $this;
    }

    public function removeSocialLink(SiteSocialLink $link): static
    {
        if ($this->socialLinks->removeElement($link)) {
            if ($link->getSiteSettings() === $this) {
                $link->setSiteSettings(null);
            }
        }

        return $this;
    }

}
