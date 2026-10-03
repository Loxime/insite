<?php

namespace App\Entity;

use App\Repository\JamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JamRepository::class)]
class Jam
{
    public const MODE_RANDOM = 'random';
    public const MODE_FIXED = 'fixed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 160)]
    private ?string $name = null;

    #[ORM\Column(length: 20)]
    private string $mode = self::MODE_RANDOM;

    #[ORM\Column(options: ['default' => false])]
    private bool $permanent = false;

    #[ORM\Column(options: ['default' => true])]
    private bool $enabled = true;

    #[ORM\Column(
        type: Types::DATE_IMMUTABLE,
        nullable: true,
    )]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(
        type: Types::DATE_IMMUTABLE,
        nullable: true,
    )]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column(length: 160)]
    private ?string $sourceName = null;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $sourceUrl = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, JamScheduleDay>
     */
    #[ORM\OneToMany(
        mappedBy: 'jam',
        targetEntity: JamScheduleDay::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    #[ORM\OrderBy(['date' => 'ASC'])]
    private Collection $scheduleDays;

    public function __construct()
    {
        $this->scheduleDays = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = trim($name);

        return $this;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setMode(string $mode): static
    {
        $this->mode = $mode;

        return $this;
    }

    public function isPermanent(): bool
    {
        return $this->permanent;
    }

    public function setPermanent(bool $permanent): static
    {
        $this->permanent = $permanent;

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

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(
        ?\DateTimeImmutable $startDate,
    ): static {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(
        ?\DateTimeImmutable $endDate,
    ): static {
        $this->endDate = $endDate;

        return $this;
    }

    public function getSourceName(): ?string
    {
        return $this->sourceName;
    }

    public function setSourceName(
        string $sourceName,
    ): static {
        $this->sourceName = trim($sourceName);

        return $this;
    }

    public function getSourceUrl(): ?string
    {
        return $this->sourceUrl;
    }

    public function setSourceUrl(
        ?string $sourceUrl,
    ): static {
        $sourceUrl = trim((string) $sourceUrl);

        $this->sourceUrl = $sourceUrl === ''
            ? null
            : $sourceUrl;

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
     * @return Collection<int, JamScheduleDay>
     */
    public function getScheduleDays(): Collection
    {
        return $this->scheduleDays;
    }

    public function addScheduleDay(
        JamScheduleDay $day,
    ): static {
        if (!$this->scheduleDays->contains($day)) {
            $this->scheduleDays->add($day);
            $day->setJam($this);
        }

        return $this;
    }

    public function removeScheduleDay(
        JamScheduleDay $day,
    ): static {
        if ($this->scheduleDays->removeElement($day)) {
            if ($day->getJam() === $this) {
                $day->setJam(null);
            }
        }

        return $this;
    }
}
