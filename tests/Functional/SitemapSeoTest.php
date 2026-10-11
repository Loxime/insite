<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\AboutProfile;
use App\Entity\ChangelogEntry;
use App\Entity\Game;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SitemapSeoTest extends WebTestCase
{
    private const DOMAIN = 'https://inquest.falchero.fr';

    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

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
            throw new \RuntimeException(
                'Base de test inattendue : ' . $database,
            );
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

    public function testSitemapContainsPublicRoutes(): void
    {
        $urls = $this->getSitemapUrls();

        foreach ([
            '/',
            '/about',
            '/games',
            '/faq',
            '/careers',
            '/contact',
        ] as $path) {
            self::assertContains(self::DOMAIN . $path, $urls);
        }

        self::assertCount(count(array_unique($urls)), $urls);

        foreach ($urls as $url) {
            self::assertStringStartsWith(self::DOMAIN . '/', $url);
            self::assertStringNotContainsString('/admin', $url);
            self::assertStringNotContainsString('/jam/', $url);
        }
    }

    public function testOnlyPublishedAndVisibleContentAppears(): void
    {
        $token = bin2hex(random_bytes(6));

        $author = (new User())
            ->setEmail('seo-' . $token . '@example.test')
            ->setUsername('Auteur SEO')
            ->setPassword('temporary-test-password')
            ->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($author);

        foreach (['published', 'draft'] as $status) {
            $post = (new Post())
                ->setTitle('Article ' . $status)
                ->setSlug('seo-post-' . $status . '-' . $token)
                ->setContent('<p>Article de test SEO.</p>')
                ->setStatus($status)
                ->setCreatedAt(new \DateTimeImmutable())
                ->setAuthor($author);

            $game = (new Game())
                ->setTitle('Jeu ' . $status)
                ->setSlug('seo-game-' . $status . '-' . $token)
                ->setDescription('<p>Description du jeu.</p>')
                ->setStatus($status)
                ->setCreatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($post);
            $this->entityManager->persist($game);
        }

        foreach ([true, false] as $enabled) {
            $slug = 'seo-profile-'
                . ($enabled ? 'visible-' : 'hidden-')
                . $token;

            $profile = (new AboutProfile())
                ->setFirstName('Profil')
                ->setLastName('SEO')
                ->setSlug($slug)
                ->setRole('Développeur')
                ->setDescription('Profil de test')
                ->setContent('Présentation du profil')
                ->setEnabled($enabled);

            $this->entityManager->persist($profile);
        }

        $publishedVersion = 'seo-published-' . $token;
        $draftVersion = 'seo-draft-' . $token;

        $publishedEntry = (new ChangelogEntry())
            ->setVersion($publishedVersion)
            ->setTitle('Version publiée')
            ->setText('<p>Nouvelle version.</p>')
            ->publish();

        $draftEntry = (new ChangelogEntry())
            ->setVersion($draftVersion)
            ->setTitle('Version non publiée')
            ->setText('<p>Version brouillon.</p>');

        $this->entityManager->persist($publishedEntry);
        $this->entityManager->persist($draftEntry);
        $this->entityManager->flush();

        $urls = $this->getSitemapUrls();

        self::assertContains(
            self::DOMAIN . '/blog/seo-post-published-' . $token,
            $urls,
        );

        self::assertNotContains(
            self::DOMAIN . '/blog/seo-post-draft-' . $token,
            $urls,
        );

        self::assertContains(
            self::DOMAIN . '/games/seo-game-published-' . $token,
            $urls,
        );

        self::assertNotContains(
            self::DOMAIN . '/games/seo-game-draft-' . $token,
            $urls,
        );

        self::assertContains(
            self::DOMAIN . '/about/profile/seo-profile-visible-' . $token,
            $urls,
        );

        self::assertNotContains(
            self::DOMAIN . '/about/profile/seo-profile-hidden-' . $token,
            $urls,
        );

        self::assertContains(
            self::DOMAIN . '/changelog/' . $publishedVersion,
            $urls,
        );

        self::assertNotContains(
            self::DOMAIN . '/changelog/' . $draftVersion,
            $urls,
        );

        self::assertNotContains(
            self::DOMAIN . '/changelog',
            $urls,
        );
    }

    public function testArticleUsesCustomSeoMetadata(): void
    {
        $token = bin2hex(random_bytes(6));
        $slug = 'seo-article-' . $token;

        $author = (new User())
            ->setEmail('seo-article-' . $token . '@example.test')
            ->setUsername('Auteur SEO')
            ->setPassword('temporary-test-password')
            ->setCreatedAt(new \DateTimeImmutable());

        $post = (new Post())
            ->setTitle('Titre visible')
            ->setSlug($slug)
            ->setContent('<p>Contenu de l’article.</p>')
            ->setStatus('published')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setAuthor($author)
            ->setMetaTitle('Titre SEO personnalisé')
            ->setMetaDescription('Description SEO personnalisée')
            ->setImageKey('blog/seo-test.png');

        $this->entityManager->persist($author);
        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $crawler = $this->client->request(
            'GET',
            '/blog/' . $slug,
        );

        self::assertResponseIsSuccessful();

        self::assertSame(
            'Titre SEO personnalisé',
            trim($crawler->filter('title')->text()),
        );

        self::assertSame(
            'Description SEO personnalisée',
            $crawler->filter('meta[name="description"]')->attr('content'),
        );

        self::assertSame(
            self::DOMAIN . '/blog/' . $slug,
            $crawler->filter('link[rel="canonical"]')->attr('href'),
        );

        self::assertSame(
            'article',
            $crawler->filter('meta[property="og:type"]')->attr('content'),
        );

        self::assertSame(
            self::DOMAIN . '/media/blog/seo-test.png',
            $crawler->filter('meta[property="og:image"]')->attr('content'),
        );
    }

    public function testGameUsesSummaryAndImage(): void
    {
        $token = bin2hex(random_bytes(6));
        $slug = 'seo-game-' . $token;

        $game = (new Game())
            ->setTitle('Jeu SEO')
            ->setSlug($slug)
            ->setSummary('Résumé SEO du jeu')
            ->setDescription('<p>Description complète du jeu.</p>')
            ->setStatus('published')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setImageKey('games/seo-test.png');

        $this->entityManager->persist($game);
        $this->entityManager->flush();

        $crawler = $this->client->request(
            'GET',
            '/games/' . $slug,
        );

        self::assertResponseIsSuccessful();

        self::assertSame(
            'Résumé SEO du jeu',
            $crawler->filter('meta[name="description"]')->attr('content'),
        );

        self::assertSame(
            self::DOMAIN . '/games/' . $slug,
            $crawler->filter('link[rel="canonical"]')->attr('href'),
        );

        self::assertSame(
            self::DOMAIN . '/media/games/seo-test.png',
            $crawler->filter('meta[property="og:image"]')->attr('content'),
        );
    }

    /**
     * @return list<string>
     */
    private function getSitemapUrls(): array
    {
        $this->client->request('GET', '/sitemap.xml');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame(
            'Content-Type',
            'application/xml; charset=UTF-8',
        );

        $xml = (string) $this->client->getResponse()->getContent();

        $document = new \DOMDocument();

        self::assertTrue($document->loadXML($xml));

        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace(
            's',
            'http://www.sitemaps.org/schemas/sitemap/0.9',
        );

        $nodes = $xpath->query('//s:url/s:loc');

        self::assertNotFalse($nodes);

        $urls = [];

        foreach ($nodes as $node) {
            $urls[] = trim($node->textContent);
        }

        return $urls;
    }
}
