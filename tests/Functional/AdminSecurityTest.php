<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AdminSecurityTest extends WebTestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function protectedPaths(): iterable
    {
        yield 'Administration' => ['/admin'];
        yield 'Blog' => ['/admin/blog'];
        yield 'Games' => ['/admin/games'];
        yield 'About' => ['/admin/about'];
        yield 'Team profiles' => ['/admin/about/profiles'];
        yield 'Changelog' => ['/admin/changelog'];
        yield 'Announcements' => ['/admin/announcements'];
        yield 'Users' => ['/admin/users'];
    }

    #[DataProvider('protectedPaths')]
    public function testAnonymousAccessRedirectsToLogin(
        string $path,
    ): void {
        $client = static::createClient([
            'environment' => 'test',
        ]);

        self::assertSame(
            'test',
            self::$kernel->getEnvironment(),
        );

        $connection = static::getContainer()
            ->get('doctrine')
            ->getConnection();

        self::assertSame(
            'insite_dev_test',
            $connection->getParams()['dbname'] ?? null,
        );

        $client->request('GET', $path);

        self::assertResponseRedirects(
            '/admin/login',
            302,
        );
    }

    public function testLoginPageIsPublic(): void
    {
        $client = static::createClient([
            'environment' => 'test',
        ]);

        $client->request('GET', '/admin/login');

        self::assertResponseIsSuccessful();
    }
}
