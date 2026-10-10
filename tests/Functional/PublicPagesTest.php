<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PublicPagesTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        self::assertSame(
            'test',
            $_ENV['APP_ENV'] ?? null,
            'PHPUnit doit démarrer en environnement test.',
        );

        $this->client = static::createClient([
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
            'Les tests ne doivent jamais utiliser insite_dev.',
        );
    }

    public function testHomepageIsAccessible(): void
    {
        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
    }

    public function testAboutPageIsAccessible(): void
    {
        $this->client->request('GET', '/about');

        self::assertResponseIsSuccessful();
    }

    public function testContactPageIsAccessible(): void
    {
        $this->client->request('GET', '/contact');

        self::assertResponseIsSuccessful();
    }

    public function testUnknownProfileReturns404(): void
    {
        $this->client->request(
            'GET',
            '/about/profile/profil-inexistant',
        );

        self::assertResponseStatusCodeSame(404);
    }

    public function testAnonymousAdminAccessRedirects(): void
    {
        $this->client->request('GET', '/admin');

        self::assertResponseRedirects();
    }
}
