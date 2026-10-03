<?php

namespace App\Controller\FrontOffice;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    private const POSTS_PER_PAGE = 5;

    #[Route(
        '/',
        name: 'app_frontoffice_home_show',
        methods: ['GET'],
    )]
    public function show(
        Request $request,
        PostRepository $postRepository,
    ): Response {
        $page = max(
            1,
            $request->query->getInt('page', 1),
        );

        $totalPosts = $postRepository->countPublished();

        $totalPages = max(
            1,
            (int) ceil(
                $totalPosts / self::POSTS_PER_PAGE,
            ),
        );

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        return $this->render(
            'front_office/home.html.twig',
            [
                'posts' => $postRepository->findPublishedPage(
                    $page,
                    self::POSTS_PER_PAGE,
                ),
                'page' => $page,
                'totalPages' => $totalPages,
                'totalPosts' => $totalPosts,
            ],
        );
    }
}
