<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ChangelogEntryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/changelog', name: 'changelog_')]
final class ChangelogController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        ChangelogEntryRepository $repository,
    ): Response {
        return $this->render('changelog/show.html.twig', [
            'entry' => $repository->findLatestPublished(),
        ]);
    }

    #[Route(
        '/{version}',
        name: 'show',
        requirements: ['version' => '[A-Za-z0-9][A-Za-z0-9._-]*'],
        methods: ['GET'],
    )]
    public function show(
        string $version,
        ChangelogEntryRepository $repository,
    ): Response {
        $entry = $repository->findPublishedByVersion($version);

        if ($entry === null) {
            throw $this->createNotFoundException(
                'Cette version du changelog est introuvable.',
            );
        }

        return $this->render('changelog/show.html.twig', [
            'entry' => $entry,
        ]);
    }
}
