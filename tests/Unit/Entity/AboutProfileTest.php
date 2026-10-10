<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\AboutProfile;
use PHPUnit\Framework\TestCase;

final class AboutProfileTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $profile = new AboutProfile();

        self::assertNull($profile->getId());
        self::assertSame('', $profile->getFirstName());
        self::assertSame('', $profile->getLastName());
        self::assertSame('', $profile->getDisplayName());
        self::assertSame('', $profile->getSlug());
        self::assertSame('', $profile->getRole());
        self::assertSame('', $profile->getDescription());
        self::assertSame('', $profile->getContent());
        self::assertNull($profile->getImageKey());
        self::assertNull($profile->getAge());
        self::assertSame([], $profile->getLanguages());
        self::assertSame([], $profile->getSkills());
        self::assertSame(0, $profile->getDisplayOrder());
        self::assertTrue($profile->isEnabled());
        self::assertInstanceOf(
            \DateTimeImmutable::class,
            $profile->getUpdatedAt(),
        );
    }

    public function testDisplayName(): void
    {
        $profile = new AboutProfile();

        $profile->setFirstName('Ada');
        $profile->setLastName('Lovelace');

        self::assertSame('Ada Lovelace', $profile->getDisplayName());

        $profile->setLastName('');

        self::assertSame('Ada', $profile->getDisplayName());

        $profile->setFirstName('');

        self::assertSame('', $profile->getDisplayName());
    }

    public function testProfileInformation(): void
    {
        $profile = new AboutProfile();

        $result = $profile
            ->setFirstName('Jean')
            ->setLastName('Dupont')
            ->setSlug('jean-dupont')
            ->setRole('Game Designer')
            ->setDescription('Présentation courte')
            ->setContent('<p>Biographie complète</p>');

        self::assertSame($profile, $result);
        self::assertSame('Jean Dupont', $profile->getDisplayName());
        self::assertSame('jean-dupont', $profile->getSlug());
        self::assertSame('Game Designer', $profile->getRole());
        self::assertSame(
            'Présentation courte',
            $profile->getDescription(),
        );
        self::assertSame(
            '<p>Biographie complète</p>',
            $profile->getContent(),
        );
    }

    public function testOptionalInformation(): void
    {
        $profile = new AboutProfile();

        $profile->setImageKey('about/profiles/jean.webp');
        $profile->setAge(26);

        self::assertSame(
            'about/profiles/jean.webp',
            $profile->getImageKey(),
        );
        self::assertSame(26, $profile->getAge());

        $profile->setImageKey(null);
        $profile->setAge(null);

        self::assertNull($profile->getImageKey());
        self::assertNull($profile->getAge());
    }

    public function testLanguagesAndSkills(): void
    {
        $profile = new AboutProfile();

        $profile->setLanguages([
            'Français',
            'Anglais',
        ]);

        $profile->setSkills([
            'Symfony',
            'PHP',
            'Game Design',
        ]);

        self::assertSame(
            ['Français', 'Anglais'],
            $profile->getLanguages(),
        );

        self::assertSame(
            ['Symfony', 'PHP', 'Game Design'],
            $profile->getSkills(),
        );

        $profile->setLanguages([]);
        $profile->setSkills([]);

        self::assertSame([], $profile->getLanguages());
        self::assertSame([], $profile->getSkills());
    }

    public function testVisibilityAndDisplayOrder(): void
    {
        $profile = new AboutProfile();

        self::assertTrue($profile->isEnabled());
        self::assertSame(0, $profile->getDisplayOrder());

        $profile->setEnabled(false);
        $profile->setDisplayOrder(5);

        self::assertFalse($profile->isEnabled());
        self::assertSame(5, $profile->getDisplayOrder());

        $profile->setEnabled(true);
        $profile->setDisplayOrder(0);

        self::assertTrue($profile->isEnabled());
        self::assertSame(0, $profile->getDisplayOrder());
    }

    public function testUpdatedAt(): void
    {
        $profile = new AboutProfile();

        $initial = $profile->getUpdatedAt();

        self::assertSame(
            'UTC',
            $initial->getTimezone()->getName(),
        );

        $result = $profile->touchUpdatedAt();
        $updated = $profile->getUpdatedAt();

        self::assertSame($profile, $result);
        self::assertNotSame($initial, $updated);
        self::assertSame(
            'UTC',
            $updated->getTimezone()->getName(),
        );
    }
}
