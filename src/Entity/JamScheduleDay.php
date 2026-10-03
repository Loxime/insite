<?php

namespace App\Entity;

use App\Repository\JamScheduleDayRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JamScheduleDayRepository::class)]
#[ORM\Table(
    name: 'jam_schedule_day',
    uniqueConstraints: [
        new ORM\UniqueConstraint(
            name: 'uniq_jam_schedule_day',
            columns: ['jam_id', 'jam_date'],
        ),
    ],
)]
class JamScheduleDay
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(
        inversedBy: 'scheduleDays',
    )]
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

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(
        nullable: true,
        onDelete: 'SET NULL',
    )]
    private ?JamTheme $theme = null;

    #[ORM\Column(length: 120)]
    private ?string $themeLabel = null;

    #[ORM\Column(length: 7)]
    private string $color1 = '#2292A4';

    #[ORM\Column(length: 7)]
    private string $color2 = '#0F0A0A';

    #[ORM\Column(length: 7)]
    private string $color3 = '#FFFFFF';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJam(): ?Jam
    {
        return $this->jam;
    }

    public function setJam(?Jam $jam): static
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

    public function getTheme(): ?JamTheme
    {
        return $this->theme;
    }

    public function setTheme(
        ?JamTheme $theme,
    ): static {
        $this->theme = $theme;

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

    public function getColor1(): string
    {
        return $this->color1;
    }

    public function setColor1(string $color): static
    {
        $this->color1 = strtoupper($color);

        return $this;
    }

    public function getColor2(): string
    {
        return $this->color2;
    }

    public function setColor2(string $color): static
    {
        $this->color2 = strtoupper($color);

        return $this;
    }

    public function getColor3(): string
    {
        return $this->color3;
    }

    public function setColor3(string $color): static
    {
        $this->color3 = strtoupper($color);

        return $this;
    }
}
