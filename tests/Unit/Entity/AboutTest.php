<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\About;
use PHPUnit\Framework\TestCase;

final class AboutTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $about = new About();

        self::assertNull($about->getId());
        self::assertSame('À propos', $about->getTitle());
        self::assertNull($about->getContent());
        self::assertNull($about->getSecondaryContent());
        self::assertNull($about->getImageKey());
        self::assertNull($about->getUpdatedAt());
        self::assertCount(0, $about->getSocialLinks());
    }

    public function testContentsAreIndependent(): void
    {
        $about = new About();

        $about->setContent('<p>Présentation du studio</p>');
        $about->setSecondaryContent('<p>Notre philosophie</p>');

        self::assertSame(
            '<p>Présentation du studio</p>',
            $about->getContent(),
        );

        self::assertSame(
            '<p>Notre philosophie</p>',
            $about->getSecondaryContent(),
        );

        $about->setSecondaryContent(null);

        self::assertNull($about->getSecondaryContent());
        self::assertSame(
            '<p>Présentation du studio</p>',
            $about->getContent(),
        );
    }

    public function testTitleCanBeUpdated(): void
    {
        $about = new About();

        $result = $about->setTitle('Notre studio');

        self::assertSame($about, $result);
        self::assertSame('Notre studio', $about->getTitle());
    }

    public function testImageKeyCanBeUpdatedAndRemoved(): void
    {
        $about = new About();

        $about->setImageKey('about/studio.webp');

        self::assertSame(
            'about/studio.webp',
            $about->getImageKey(),
        );

        $about->setImageKey(null);

        self::assertNull($about->getImageKey());
    }

    public function testUpdatedAtCanBeSet(): void
    {
        $about = new About();
        $date = new \DateTimeImmutable('2026-10-10 10:00:00');

        $about->setUpdatedAt($date);

        self::assertSame($date, $about->getUpdatedAt());
    }
}
