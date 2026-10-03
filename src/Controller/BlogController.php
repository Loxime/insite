<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BlogController extends AbstractController
{
    #[Route(
        '/blog/{slug}',
        name: 'blog_show',
        methods: ['GET'],
    )]
    public function show(
        string $slug,
        PostRepository $postRepository,
    ): Response {
        $post = $postRepository->findOneBy([
            'slug' => $slug,
            'status' => 'published',
        ]);

        if ($post === null) {
            throw $this->createNotFoundException();
        }

        return $this->render('blog/show.html.twig', [
            'post' => $post,
        ]);
    }
}
