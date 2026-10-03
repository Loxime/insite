<?php

namespace App\Controller\Admin;

use App\Entity\PostCategory;
use App\Form\PostCategoryType;
use App\Repository\PostCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/tags', name: 'admin_tags_')]
final class TagController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        PostCategoryRepository $postCategoryRepository,
    ): Response {
        return $this->render('admin/tags/index.html.twig', [
            'tags' => $postCategoryRepository->findBy(
                [],
                ['libelle' => 'ASC'],
            ),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $tag = new PostCategory();

        $form = $this->createForm(
            PostCategoryType::class,
            $tag,
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tag);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Tag créé avec succès.',
            );

            return $this->redirectToRoute(
                'admin_tags_index',
                [],
                Response::HTTP_SEE_OTHER,
            );
        }

        return $this->render('admin/tags/new.html.twig', [
            'tag' => $tag,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        PostCategory $tag,
        EntityManagerInterface $entityManager,
    ): Response {
        $form = $this->createForm(
            PostCategoryType::class,
            $tag,
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Tag modifié avec succès.',
            );

            return $this->redirectToRoute(
                'admin_tags_index',
                [],
                Response::HTTP_SEE_OTHER,
            );
        }

        return $this->render('admin/tags/edit.html.twig', [
            'tag' => $tag,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(
        Request $request,
        PostCategory $tag,
        EntityManagerInterface $entityManager,
    ): Response {
        if (!$this->isCsrfTokenValid(
            'delete-tag-' . $tag->getId(),
            $request->getPayload()->getString('_token'),
        )) {
            throw $this->createAccessDeniedException();
        }

        if (!$tag->getPosts()->isEmpty()) {
            $this->addFlash(
                'error',
                'Impossible de supprimer un tag utilisé par un article.',
            );

            return $this->redirectToRoute('admin_tags_index');
        }

        $entityManager->remove($tag);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Tag supprimé avec succès.',
        );

        return $this->redirectToRoute(
            'admin_tags_index',
            [],
            Response::HTTP_SEE_OTHER,
        );
    }
}
