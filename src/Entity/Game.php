<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $summary = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 512, nullable: true)]
    private ?string $imageKey = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $displayOrder = 0;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, GameSection>
     */
    #[ORM\OneToMany(
        mappedBy: 'game',
        targetEntity: GameSection::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    #[ORM\OrderBy([
        'displayOrder' => 'ASC',
        'id' => 'ASC',
    ])]
    private Collection $sections;

    /**
     * @var Collection<int, GameLink>
     */
    #[ORM\OneToMany(
        mappedBy: 'game',
        targetEntity: GameLink::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    #[ORM\OrderBy([
        'displayOrder' => 'ASC',
        'id' => 'ASC',
    ])]
    private Collection $links;

    public function __construct()
    {
        $this->sections = new ArrayCollection();
        $this->links = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): static
    {
        $this->summary = $summary;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getDisplayOrder(): int
    {
        return $this->displayOrder;
    }

    public function setDisplayOrder(int $displayOrder): static
    {
        $this->displayOrder = $displayOrder;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(
        \DateTimeImmutable $createdAt,
    ): static {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, GameSection>
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(GameSection $section): static
    {
        if (!$this->sections->contains($section)) {
            $this->sections->add($section);
            $section->setGame($this);
        }

        return $this;
    }

    public function removeSection(GameSection $section): static
    {
        if ($this->sections->removeElement($section)) {
            if ($section->getGame() === $this) {
                $section->setGame(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, GameLink>
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }

    public function addLink(GameLink $link): static
    {
        if (!$this->links->contains($link)) {
            $this->links->add($link);
            $link->setGame($this);
        }

        return $this;
    }

    public function removeLink(GameLink $link): static
    {
        if ($this->links->removeElement($link)) {
            if ($link->getGame() === $this) {
                $link->setGame(null);
            }
        }

        return $this;
    }


}
