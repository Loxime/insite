<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\ChangelogEntry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ChangelogSeoTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->client->disableReboot();

        if (self::getContainer()->getParameter('kernel.environment') !== 'test') {
            throw new \RuntimeException('Environnement de test obligatoire.');
        }

        $this->entityManager = self::getContainer()->get(
            EntityManagerInterface::class,
        );

        $database = (string) $this->entityManager
            ->getConnection()
            ->fetchOne('SELECT current_database()');

        if ($database !== 'insite_dev_test') {
            throw new \RuntimeException('Base de test inattendue : ' . $database);
        }

        $this->entityManager->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        if (isset($this->entityManager)) {
            $connection = $this->entityManager->getConnection();

            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            $this->entityManager->close();
        }

        parent::tearDown();
    }

    public function testPublishedVersionGeneratesSeoMetadata(): void
    {
        $entry = $this->createEntry(true);

        $crawler = $this->client->request(
            'GET',
            '/changelog/' . $entry->getVersion(),
        );

        self::assertResponseIsSuccessful();

        $title = trim($crawler->filter('title')->text());
        $description = (string) $crawler
            ->filter('meta[name="description"]')
            ->attr('content');

        $canonical = (string) $crawler
            ->filter('link[rel="canonical"]')
            ->attr('href');

        self::assertSame(
            $entry->getTitle() . ' — Version ' .
            $entry->getVersion() . ' | INQUEST',
            $title,
        );

        self::assertStringStartsWith(
            'Version ' . $entry->getVersion() . ' : ',
            $description,
        );

        self::assertLessThanOrEqual(155, mb_strlen($description));
        self::assertStringNotContainsString('<strong>', $description);

        self::assertStringEndsWith(
            '/changelog/' . $entry->getVersion(),
            $canonical,
        );

        self::assertSame(
            'article',
            $crawler->filter('meta[property="og:type"]')->attr('content'),
        );

        self::assertSame(
            $title,
            $crawler->filter('meta[property="og:title"]')->attr('content'),
        );

        self::assertSame(
            $description,
            $crawler->filter('meta[property="og:description"]')->attr('content'),
        );

        self::assertSame(
            $canonical,
            $crawler->filter('meta[property="og:url"]')->attr('content'),
        );
    }

    public function testUnpublishedVersionIsNotAccessible(): void
    {
        $entry = $this->createEntry(false);

        $this->client->request(
            'GET',
            '/changelog/' . $entry->getVersion(),
        );

        self::assertResponseStatusCodeSame(404);
    }

    private function createEntry(bool $published): ChangelogEntry
    {
        $entry = (new ChangelogEntry())
            ->setVersion('seo-test-' . bin2hex(random_bytes(8)))
            ->setTitle('Amélioration des fonctionnalités')
            ->setText(
                '<p>Découvrez <strong>les nouveautés</strong> ' .
                str_repeat('et les améliorations du site. ', 12) .
                '</p>',
            );

        if ($published) {
            $entry->publish();
        }

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        return $entry;
    }
}
