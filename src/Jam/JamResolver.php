<?php

namespace App\Jam;

use App\Entity\Jam;
use App\Entity\JamSession;
use App\Repository\JamRepository;
use App\Repository\JamScheduleDayRepository;
use App\Repository\JamSessionRepository;
use App\Repository\JamThemeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class JamResolver
{
    private bool $resolved = false;

    /**
     * @var array<string, mixed>|null
     */
    private ?array $current = null;

    public function __construct(
        private readonly JamRepository $jamRepository,
        private readonly JamThemeRepository $themeRepository,
        private readonly JamScheduleDayRepository $scheduleRepository,
        private readonly JamSessionRepository $sessionRepository,
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('%env(JAM_TIMEZONE)%')]
        private readonly string $timezone,
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function current(): ?array
    {
        if ($this->resolved) {
            return $this->current;
        }

        $this->resolved = true;

        $zone = new \DateTimeZone($this->timezone);

        $today = (new \DateTimeImmutable(
            'now',
            $zone,
        ))->setTime(0, 0);

        $jam = $this->jamRepository
            ->findActiveSpecial($today);

        $jam ??= $this->jamRepository
            ->findPermanent();

        if (!$jam instanceof Jam) {
            return null;
        }

        if ($jam->getMode() === Jam::MODE_FIXED) {
            $day = $this->scheduleRepository
                ->findFor($jam, $today);

            if ($day === null) {
                return null;
            }

            return $this->current = [
                'jam' => $jam,
                'date' => $today,
                'theme' => $day->getThemeLabel(),
                'colors' => [
                    $day->getColor1(),
                    $day->getColor2(),
                    $day->getColor3(),
                ],
            ];
        }

        $session = $this->sessionRepository
            ->findFor($jam, $today);

        if ($session === null) {
            $session = $this->createRandomSession(
                $jam,
                $today,
            );
        }

        if ($session === null) {
            return null;
        }

        return $this->current = [
            'jam' => $jam,
            'date' => $today,
            'theme' => $session->getThemeLabel(),
            'colors' => [
                $session->getColor1(),
                $session->getColor2(),
                $session->getColor3(),
            ],
        ];
    }

    private function createRandomSession(
        Jam $jam,
        \DateTimeImmutable $today,
    ): ?JamSession {
        $themes = $this->themeRepository
            ->findEnabledOrdered();

        if ($themes === []) {
            return null;
        }

        $theme = $themes[
            random_int(
                0,
                count($themes) - 1,
            )
        ];

        $colors = $this->generateColors();

        $session = (new JamSession())
            ->setJam($jam)
            ->setDate($today)
            ->setThemeLabel(
                (string) $theme->getName(),
            )
            ->setColor1($colors[0])
            ->setColor2($colors[1])
            ->setColor3($colors[2])
            ->setCreatedAt(
                new \DateTimeImmutable(),
            );

        $this->entityManager->persist($session);
        $this->entityManager->flush();

        return $session;
    }

    /**
     * @return array{string, string, string}
     */
    private function generateColors(): array
    {
        $colors = [];

        while (count($colors) < 3) {
            $candidate = sprintf(
                '#%06X',
                random_int(0, 0xFFFFFF),
            );

            if (!in_array(
                $candidate,
                $colors,
                true,
            )) {
                $colors[] = $candidate;
            }
        }

        return [
            $colors[0],
            $colors[1],
            $colors[2],
        ];
    }
}
