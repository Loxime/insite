<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\JamChallenge;
use PHPUnit\Framework\TestCase;

final class JamChallengeTest extends TestCase
{
    public function testStatesAndThreeDayPublicationWindow(): void
    {
        $jam = (new JamChallenge())
            ->setName('InQuest 48h Challenge')
            ->setTheme('Un monde à l’envers')
            ->setStartsAt(new \DateTimeImmutable('2026-10-15T12:00:00+00:00'))
            ->setEndsAt(new \DateTimeImmutable('2026-10-17T12:00:00+00:00'));

        self::assertSame('disabled', $jam->getStateAt(new \DateTimeImmutable('2026-10-16T12:00:00+00:00')));

        $jam->setEnabled(true);
        self::assertSame('upcoming', $jam->getStateAt(new \DateTimeImmutable('2026-10-15T11:59:59+00:00')));
        self::assertSame('active', $jam->getStateAt(new \DateTimeImmutable('2026-10-15T12:00:00+00:00')));
        self::assertSame(48, $jam->getDurationHours());
        self::assertSame('submissions', $jam->getStateAt(new \DateTimeImmutable('2026-10-17T12:00:00+00:00')));
        self::assertSame('submissions', $jam->getStateAt(new \DateTimeImmutable('2026-10-20T11:59:59+00:00')));
        self::assertSame('expired', $jam->getStateAt(new \DateTimeImmutable('2026-10-20T12:00:00+00:00')));
    }
}
