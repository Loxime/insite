<?php

namespace App\Controller\Admin;

use App\Entity\FaqQuestion;
use App\Form\FaqQuestionType;
use App\Repository\FaqQuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/faq', name: 'admin_faq_')]
final class FaqController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        FaqQuestionRepository $repository,
    ): Response {
        return $this->render('admin/faq/index.html.twig', [
            'questions' => $repository->findBy(
                [],
                ['displayOrder' => 'ASC', 'id' => 'ASC'],
            ),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $question = new FaqQuestion();
        $question
            ->setCreatedAt(new \DateTimeImmutable())
            ->setEnabled(true);

        $form = $this->createForm(FaqQuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($question);
            $entityManager->flush();

            return $this->redirectToRoute('admin_faq_index');
        }

        return $this->render('admin/faq/form.html.twig', [
            'form' => $form,
            'title' => 'Nouvelle question',
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        FaqQuestion $question,
        EntityManagerInterface $entityManager,
    ): Response {
        $form = $this->createForm(FaqQuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_faq_index');
        }

        return $this->render('admin/faq/form.html.twig', [
            'form' => $form,
            'title' => 'Modifier la question',
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(
        Request $request,
        FaqQuestion $question,
        EntityManagerInterface $entityManager,
    ): Response {
        if (!$this->isCsrfTokenValid(
            'delete-faq-' . $question->getId(),
            $request->getPayload()->getString('_token'),
        )) {
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($question);
        $entityManager->flush();

        return $this->redirectToRoute('admin_faq_index');
    }
}
