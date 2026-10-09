<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ChangelogEntry;
use App\Form\ChangelogEntryType;
use App\Repository\ChangelogEntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/changelog', name: 'admin_changelog_')]
final class ChangelogController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        ChangelogEntryRepository $repository,
    ): Response {
        return $this->render('admin/changelog/index.html.twig', [
            'entries' => $repository->findBy(
                [],
                ['updatedAt' => 'DESC', 'id' => 'DESC'],
            ),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ChangelogEntryRepository $repository,
    ): Response {
        $entry = new ChangelogEntry();

        $form = $this->createForm(ChangelogEntryType::class, $entry);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->validateUniqueVersion($form, $entry, $repository);

            if ($form->isValid()) {
                $entityManager->persist($entry);
                $entityManager->flush();

                $this->addFlash('success', 'Note de patch créée.');

                return $this->redirectToRoute('admin_changelog_index');
            }
        }

        return $this->render('admin/changelog/form.html.twig', [
            'form' => $form,
            'entry' => $entry,
            'title' => 'Nouvelle note de patch',
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        ChangelogEntry $entry,
        EntityManagerInterface $entityManager,
        ChangelogEntryRepository $repository,
    ): Response {
        $form = $this->createForm(ChangelogEntryType::class, $entry);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->validateUniqueVersion($form, $entry, $repository);

            if ($form->isValid()) {
                $entry->touchUpdatedAt();
                $entityManager->flush();

                $this->addFlash('success', 'Note de patch modifiée.');

                return $this->redirectToRoute('admin_changelog_index');
            }
        }

        return $this->render('admin/changelog/form.html.twig', [
            'form' => $form,
            'entry' => $entry,
            'title' => 'Modifier la note de patch',
        ]);
    }

    #[Route('/{id}/publish', name: 'publish', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function publish(
        Request $request,
        ChangelogEntry $entry,
        EntityManagerInterface $entityManager,
    ): Response {
        if (!$this->isCsrfTokenValid(
            'publish-changelog-' . $entry->getId(),
            $request->getPayload()->getString('_token'),
        )) {
            throw $this->createAccessDeniedException();
        }

        if (!$entry->isPublished()) {
            $entry->publish();
            $entityManager->flush();

            $this->addFlash('success', 'Note de patch publiée.');
        }

        return $this->redirectToRoute('admin_changelog_index');
    }

    private function validateUniqueVersion(
        FormInterface $form,
        ChangelogEntry $entry,
        ChangelogEntryRepository $repository,
    ): void {
        if (!$form->get('version')->isValid()) {
            return;
        }

        $existing = $repository->findOneBy([
            'version' => $entry->getVersion(),
        ]);

        if ($existing !== null && $existing->getId() !== $entry->getId()) {
            $form->get('version')->addError(
                new FormError('Cette version existe déjà.'),
            );
        }
    }
}
