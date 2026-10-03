<?php

namespace App\Controller;

use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/games', name: 'games_')]
final class GameController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        GameRepository $gameRepository,
    ): Response {
        return $this->render(
            'games/index.html.twig',
            [
                'games' => (
                    $gameRepository
                        ->findPublishedOrdered()
                ),
            ],
        );
    }

    #[Route(
        '/{slug}',
        name: 'show',
        methods: ['GET'],
    )]
    public function show(
        string $slug,
        GameRepository $gameRepository,
    ): Response {
        $game = $gameRepository->findOneBy([
            'slug' => $slug,
            'status' => 'published',
        ]);

        if ($game === null) {
            throw $this->createNotFoundException();
        }

        return $this->render(
            'games/show.html.twig',
            [
                'game' => $game,
            ],
        );
    }
}
