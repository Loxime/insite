<?php

namespace App\Controller;

use App\Repository\AboutProfileRepository;
use App\Repository\AboutRepository;
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
        return $this->render('about/show.html.twig', [
            'about' => $aboutRepository->findContent(),
            'profiles' => $profileRepository->findVisibleOrdered(),
        ]);
    }

    #[Route(
        '/about/profile/{slug}',
        name: 'about_profile_show',
        requirements: ['slug' => '[a-z0-9]+(?:-[a-z0-9]+)*'],
        methods: ['GET'],
    )]
    public function showProfile(
        string $slug,
        AboutProfileRepository $profileRepository,
    ): Response {
        $profile = $profileRepository->findVisibleBySlug($slug);

        if ($profile === null) {
            throw $this->createNotFoundException(
                'Ce profil est introuvable.',
            );
        }

        return $this->render('about/profile.html.twig', [
            'profile' => $profile,
        ]);
    }
}
