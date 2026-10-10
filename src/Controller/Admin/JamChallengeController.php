<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\JamChallenge;
use App\Form\JamChallengeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/challenges', name: 'admin_challenges_')]
final class JamChallengeController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        return $this->render('admin/challenges/index.html.twig', [
            'jams' => $em->getRepository(JamChallenge::class)->findBy([], ['startsAt' => 'DESC', 'id' => 'DESC']),
            'now' => new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        return $this->handleForm($request, new JamChallenge(), $em, $slugger);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, JamChallenge $jam, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        return $this->handleForm($request, $jam, $em, $slugger);
    }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, JamChallenge $jam, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('delete-challenge-' . $jam->getId(), $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($jam);
        $em->flush();
        $this->addFlash('success', 'JAM supprimée.');

        return $this->redirectToRoute('admin_challenges_index');
    }

    private function handleForm(Request $request, JamChallenge $jam, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(JamChallengeType::class, $jam);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($jam->getStartsAt() === null || $jam->getEndsAt() === null || $jam->getEndsAt() <= $jam->getStartsAt()) {
                $form->get('endsAt')->addError(new FormError('La fin doit être après le début.'));
            }

            if (count($jam->getColors()) < 1 || count($jam->getColors()) > 3) {
                $form->get('colors')->addError(new FormError('Choisis entre une et trois couleurs.'));
            }

            foreach ($jam->getPlatforms() as $platform) {
                if (!is_array($platform) || !isset($platform['url']) || !is_string($platform['url']) || !preg_match('~^https?://~i', $platform['url'])) {
                    $form->get('platforms')->addError(new FormError('Chaque plateforme doit utiliser une URL HTTP(S).'));
                    break;
                }
            }

            if ($form->isValid()) {
                if ($jam->getSlug() === '') {
                    $base = trim(strtolower($slugger->slug($jam->getName())->toString()), '-');
                    $base = substr($base ?: 'jam', 0, 150);
                    $candidate = $base;
                    $n = 2;
                    while ($em->getRepository(JamChallenge::class)->findOneBy(['slug' => $candidate]) !== null) {
                        $candidate = $base . '-' . $n++;
                    }
                    $jam->setSlug($candidate);
                }

                $em->persist($jam);
                $em->flush();
                $this->addFlash('success', 'JAM enregistrée.');

                return $this->redirectToRoute('admin_challenges_index');
            }
        }

        return $this->render('admin/challenges/form.html.twig', [
            'form' => $form,
            'jam' => $jam,
        ]);
    }
}
