<?php

namespace App\Entity;

use App\Repository\JamSessionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JamSessionRepository::class)]
#[ORM\Table(
    name: 'jam_session',
    uniqueConstraints: [
        new ORM\UniqueConstraint(
            name: 'uniq_jam_session_day',
            columns: ['jam_id', 'jam_date'],
        ),
    ],
)]
class JamSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(
        nullable: false,
        onDelete: 'CASCADE',
    )]
    private ?Jam $jam = null;

    #[ORM\Column(
        name: 'jam_date',
        type: Types::DATE_IMMUTABLE,
    )]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(length: 120)]
    private ?string $themeLabel = null;

    #[ORM\Column(length: 7)]
    private ?string $color1 = null;

    #[ORM\Column(length: 7)]
    private ?string $color2 = null;

    #[ORM\Column(length: 7)]
    private ?string $color3 = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJam(): ?Jam
    {
        return $this->jam;
    }

    public function setJam(Jam $jam): static
    {
        $this->jam = $jam;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(
        \DateTimeImmutable $date,
    ): static {
        $this->date = $date;

        return $this;
    }

    public function getThemeLabel(): ?string
    {
        return $this->themeLabel;
    }

    public function setThemeLabel(
        string $themeLabel,
    ): static {
        $this->themeLabel = $themeLabel;

        return $this;
    }

    public function getColor1(): ?string
    {
        return $this->color1;
    }

    public function setColor1(string $color): static
    {
        $this->color1 = $color;

        return $this;
    }

    public function getColor2(): ?string
    {
        return $this->color2;
    }

    public function setColor2(string $color): static
    {
        $this->color2 = $color;

        return $this;
    }

    public function getColor3(): ?string
    {
        return $this->color3;
    }

    public function setColor3(string $color): static
    {
        $this->color3 = $color;

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
}
