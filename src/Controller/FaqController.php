<?php

namespace App\Controller;

use App\Repository\FaqQuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FaqController extends AbstractController
{
    #[Route('/faq', name: 'faq_index', methods: ['GET'])]
    public function index(
        FaqQuestionRepository $repository,
    ): Response {
        return $this->render('faq/index.html.twig', [
            'questions' => $repository->findPublicQuestions(),
        ]);
    }
}
