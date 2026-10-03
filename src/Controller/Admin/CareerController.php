<?php

namespace App\Controller\Admin;

use App\Entity\CareerOffer;
use App\Form\CareerOfferType;
use App\Repository\CareerOfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/careers', name: 'admin_careers_')]
final class CareerController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        CareerOfferRepository $repository,
    ): Response {
        return $this->render('admin/careers/index.html.twig', [
            'offers' => $repository->findBy(
                [],
                ['displayOrder' => 'ASC', 'id' => 'DESC'],
            ),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
    ): Response {
        $offer = new CareerOffer();

        $offer
            ->setCreatedAt(new \DateTimeImmutable())
            ->setEnabled(true);

        $form = $this->createForm(CareerOfferType::class, $offer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($offer);
            $em->flush();

            return $this->redirectToRoute('admin_careers_index');
        }

        return $this->render('admin/careers/form.html.twig', [
            'form' => $form,
            'title' => 'Nouvelle offre',
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        CareerOffer $offer,
        EntityManagerInterface $em,
    ): Response {
        $form = $this->createForm(CareerOfferType::class, $offer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('admin_careers_index');
        }

        return $this->render('admin/careers/form.html.twig', [
            'form' => $form,
            'title' => 'Modifier l’offre',
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(
        Request $request,
        CareerOffer $offer,
        EntityManagerInterface $em,
    ): Response {
        if (!$this->isCsrfTokenValid(
            'delete-career-' . $offer->getId(),
            $request->getPayload()->getString('_token'),
        )) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($offer);
        $em->flush();

        return $this->redirectToRoute('admin_careers_index');
    }
}
