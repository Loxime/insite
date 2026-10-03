<?php

namespace App\Controller;

use App\Repository\CareerOfferRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CareerController extends AbstractController
{
    #[Route('/careers', name: 'careers_index', methods: ['GET'])]
    public function index(
        CareerOfferRepository $repository,
    ): Response {
        return $this->render('careers/index.html.twig', [
            'offers' => $repository->findPublicOffers(),
        ]);
    }
}
