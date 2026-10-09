<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\TemporaryAnnouncement;
use App\Form\TemporaryAnnouncementType;
use App\Repository\TemporaryAnnouncementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/announcements', name: 'admin_announcements_')]
final class TemporaryAnnouncementController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        TemporaryAnnouncementRepository $repository,
    ): Response {
        return $this->render('admin/announcements/index.html.twig', [
            'announcements' => $repository->findBy(
                [],
                ['createdAt' => 'DESC', 'id' => 'DESC'],
            ),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $announcement = new TemporaryAnnouncement();

        $form = $this->createForm(
            TemporaryAnnouncementType::class,
            $announcement,
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->validateSchedule($form, $announcement);

            if ($form->isValid()) {
                $entityManager->persist($announcement);
                $entityManager->flush();

                $this->addFlash('success', 'Annonce créée.');

                return $this->redirectToRoute('admin_announcements_index');
            }
        }

        return $this->render('admin/announcements/form.html.twig', [
            'form' => $form,
            'announcement' => $announcement,
            'title' => 'Nouvelle annonce',
        ]);
    }

    #[Route(
        '/{id}/edit',
        name: 'edit',
        requirements: ['id' => '\d+'],
        methods: ['GET', 'POST'],
    )]
    public function edit(
        Request $request,
        TemporaryAnnouncement $announcement,
        EntityManagerInterface $entityManager,
    ): Response {
        $form = $this->createForm(
            TemporaryAnnouncementType::class,
            $announcement,
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->validateSchedule($form, $announcement);

            if ($form->isValid()) {
                $announcement->touchUpdatedAt();
                $entityManager->flush();

                $this->addFlash('success', 'Annonce modifiée.');

                return $this->redirectToRoute('admin_announcements_index');
            }
        }

        return $this->render('admin/announcements/form.html.twig', [
            'form' => $form,
            'announcement' => $announcement,
            'title' => 'Modifier une annonce',
        ]);
    }

    #[Route(
        '/{id}/delete',
        name: 'delete',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
    )]
    public function delete(
        Request $request,
        TemporaryAnnouncement $announcement,
        EntityManagerInterface $entityManager,
    ): Response {
        if (!$this->isCsrfTokenValid(
            'delete-announcement-' . $announcement->getId(),
            $request->getPayload()->getString('_token'),
        )) {
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($announcement);
        $entityManager->flush();

        $this->addFlash('success', 'Annonce supprimée.');

        return $this->redirectToRoute('admin_announcements_index');
    }

    private function validateSchedule(
        FormInterface $form,
        TemporaryAnnouncement $announcement,
    ): void {
        $startsAt = $announcement->getStartsAt();
        $endsAt = $announcement->getEndsAt();

        if (
            $startsAt !== null
            && $endsAt !== null
            && $endsAt <= $startsAt
        ) {
            $form->get('endsAt')->addError(
                new FormError(
                    'La fin doit être postérieure au début de publication.',
                ),
            );
        }
    }
}
