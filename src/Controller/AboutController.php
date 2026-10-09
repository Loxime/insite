<?php

namespace App\Controller;

use App\Repository\AboutRepository;
use App\Repository\AboutProfileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AboutController extends AbstractController
{
    #[Route(
        '/about',
        name: 'about_show',
        methods: ['GET'],
    )]
    public function show(
        AboutRepository $aboutRepository,
        AboutProfileRepository $profileRepository,
    ): Response {
        return $this->render(
            'about/show.html.twig',
            [
                'about' => $aboutRepository->findContent(),
                'profiles' => $profileRepository->findVisibleOrdered(),
            ],
        );
    }
}
