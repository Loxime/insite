<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\TemporaryAnnouncement;
use PHPUnit\Framework\TestCase;

final class TemporaryAnnouncementTest extends TestCase
{
    public function testDefaultTypeIsPopup(): void
    {
        $announcement = new TemporaryAnnouncement();

        self::assertSame('popup', $announcement->getType());
        self::assertNull($announcement->getBannerText());
        self::assertFalse($announcement->isEnabled());
    }

    public function testBannerCanBeConfigured(): void
    {
        $announcement = (new TemporaryAnnouncement())
            ->setType(TemporaryAnnouncement::TYPE_BANNER)
            ->setBannerText('  Nouveau jeu disponible !  ')
            ->setButtonUrl('/games')
            ->setEnabled(true);

        self::assertSame('banner', $announcement->getType());
        self::assertSame(
            'Nouveau jeu disponible !',
            $announcement->getBannerText(),
        );
        self::assertSame('/games', $announcement->getButtonUrl());
        self::assertTrue($announcement->isEnabled());
    }

    public function testBannerTextCanBeCleared(): void
    {
        $announcement = (new TemporaryAnnouncement())
            ->setBannerText('Une annonce');

        $announcement->setBannerText(' ');

        self::assertNull($announcement->getBannerText());
    }

    public function testUnknownTypeIsRejected(): void
    {
        $announcement = new TemporaryAnnouncement();

        $this->expectException(\InvalidArgumentException::class);

        $announcement->setType('unknown');
    }

    public function testTypeCanBeChanged(): void
    {
        $announcement = new TemporaryAnnouncement();

        $announcement->setType(TemporaryAnnouncement::TYPE_BANNER);
        $announcement->setType(TemporaryAnnouncement::TYPE_POPUP);

        self::assertSame('popup', $announcement->getType());
    }
}
