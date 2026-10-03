<?php

namespace App\Controller\Admin;

use App\Entity\SiteSettings;
use App\Form\SiteSettingsType;
use App\Repository\SiteSettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SiteSettingsController extends AbstractController
{
    #[Route(
        '/admin/site',
        name: 'admin_site_edit',
        methods: ['GET', 'POST'],
    )]
    public function edit(
        Request $request,
        SiteSettingsRepository $repository,
        EntityManagerInterface $entityManager,
    ): Response {
        $settings = $repository->findSettings();

        if ($settings === null) {
            $settings = new SiteSettings();

            $settings->setUpdatedAt(
                new \DateTimeImmutable(),
            );

            $entityManager->persist($settings);
        }

        $form = $this->createForm(
            SiteSettingsType::class,
            $settings,
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $settings->setUpdatedAt(
                new \DateTimeImmutable(),
            );

            $entityManager->persist($settings);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Configuration du site enregistrée.',
            );

            return $this->redirectToRoute(
                'admin_site_edit',
            );
        }

        return $this->render(
            'admin/site/edit.html.twig',
            [
                'form' => $form,
            ],
        );
    }
}
