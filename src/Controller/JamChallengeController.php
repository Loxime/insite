<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\JamChallenge;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class JamChallengeController extends AbstractController
{
    #[Route('/jam/{slug}', name: 'jam_challenge_show', requirements: ['slug' => '[a-z0-9-]+'], methods: ['GET'])]
    public function show(string $slug, EntityManagerInterface $em): Response
    {
        $jam = $em->getRepository(JamChallenge::class)->findOneBy(['slug' => $slug]);
        if (!$jam instanceof JamChallenge) {
            throw $this->createNotFoundException();
        }

        $state = $jam->getStateAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
        if ($state === 'disabled' || $state === 'expired') {
            throw $this->createNotFoundException();
        }

        $response = $this->render('jam/show.html.twig', ['jam' => $jam, 'state' => $state]);
        // Le compte a rebours et les etats de la JAM ne doivent pas etre mis en cache.
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
