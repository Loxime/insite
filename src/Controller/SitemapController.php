<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\AboutProfileRepository;
use App\Repository\ChangelogEntryRepository;
use App\Repository\GameRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SitemapController extends AbstractController
{
    private const BASE_URL = 'https://inquest.falchero.fr';

    #[Route(
        '/sitemap.xml',
        name: 'sitemap',
        methods: ['GET'],
    )]
    public function index(
        UrlGeneratorInterface $router,
        PostRepository $postRepository,
        GameRepository $gameRepository,
        AboutProfileRepository $profileRepository,
        ChangelogEntryRepository $changelogRepository,
    ): Response {
        $routes = [
            'app_frontoffice_home_show',
            'about_show',
            'games_index',
            'faq_index',
            'careers_index',
            'contact',
        ];

        $paths = [];

        foreach ($routes as $route) {
            $paths[] = $router->generate($route);
        }

        // Articles publiés.
        foreach (
            $postRepository->findBy(
                ['status' => 'published'],
                ['createdAt' => 'DESC'],
            ) as $post
        ) {
            $paths[] = $router->generate('blog_show', [
                'slug' => $post->getSlug(),
            ]);
        }

        // Jeux publiés.
        foreach ($gameRepository->findPublishedOrdered() as $game) {
            $paths[] = $router->generate('games_show', [
                'slug' => $game->getSlug(),
            ]);
        }

        // Profils publics activés.
        foreach ($profileRepository->findVisibleOrdered() as $profile) {
            $paths[] = $router->generate('about_profile_show', [
                'slug' => $profile->getSlug(),
            ]);
        }

        // Sans version publiée, l'index reste une page canonique.
        if ($changelogRepository->findLatestPublished() === null) {
            $paths[] = $router->generate('changelog_index');
        }

        // Versions publiées du changelog.
        foreach ($changelogRepository->findPublishedOrdered() as $entry) {
            $paths[] = $router->generate('changelog_show', [
                'version' => $entry->getVersion(),
            ]);
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach (array_unique($paths) as $path) {
            $url = htmlspecialchars(
                self::BASE_URL . $path,
                ENT_XML1 | ENT_QUOTES,
                'UTF-8',
            );

            $xml .= "    <url>\n";
            $xml .= "        <loc>{$url}</loc>\n";
            $xml .= "    </url>\n";
        }

        $xml .= "</urlset>\n";

        return new Response(
            $xml,
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/xml; charset=UTF-8',
            ],
        );
    }
}
