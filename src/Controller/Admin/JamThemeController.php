<?php

namespace App\Controller\Admin;

use App\Entity\JamTheme;
use App\Form\JamThemeType;
use App\Repository\JamThemeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/jam/themes', name: 'admin_jam_themes_')]
final class JamThemeController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        JamThemeRepository $repository,
    ): Response {
        return $this->render(
            'admin/jam/themes/index.html.twig',
            [
                'themes' => $repository->findBy(
                    [],
                    ['name' => 'ASC'],
                ),
            ],
        );
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
    ): Response {
        $theme = (new JamTheme())
            ->setEnabled(true)
            ->setCreatedAt(
                new \DateTimeImmutable(),
            );

        $form = $this->createForm(
            JamThemeType::class,
            $theme,
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($theme);
            $em->flush();

            $this->addFlash(
                'success',
                'Thème ajouté.',
            );

            return $this->redirectToRoute(
                'admin_jam_themes_index',
            );
        }

        return $this->render(
            'admin/jam/themes/form.html.twig',
            [
                'form' => $form,
                'title' => 'Nouveau thème',
            ],
        );
    }

    #[Route(
        '/{id}/edit',
        name: 'edit',
        requirements: ['id' => '\d+'],
        methods: ['GET', 'POST'],
    )]
    public function edit(
        Request $request,
        JamTheme $theme,
        EntityManagerInterface $em,
    ): Response {
        $form = $this->createForm(
            JamThemeType::class,
            $theme,
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash(
                'success',
                'Thème modifié.',
            );

            return $this->redirectToRoute(
                'admin_jam_themes_index',
            );
        }

        return $this->render(
            'admin/jam/themes/form.html.twig',
            [
                'form' => $form,
                'title' => 'Modifier le thème',
            ],
        );
    }

    #[Route(
        '/{id}/delete',
        name: 'delete',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
    )]
    public function delete(
        Request $request,
        JamTheme $theme,
        EntityManagerInterface $em,
    ): Response {
        if (!$this->isCsrfTokenValid(
            'delete-jam-theme-' . $theme->getId(),
            $request->getPayload()->getString('_token'),
        )) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($theme);
        $em->flush();

        $this->addFlash(
            'success',
            'Thème supprimé.',
        );

        return $this->redirectToRoute(
            'admin_jam_themes_index',
        );
    }
}
