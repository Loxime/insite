<?php

namespace App\Controller\Admin;

use App\Entity\Jam;
use App\Form\JamType;
use App\Repository\JamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/jam', name: 'admin_jam_')]
final class JamController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        JamRepository $repository,
    ): Response {
        return $this->render(
            'admin/jam/index.html.twig',
            [
                'jams' => $repository->findBy(
                    [],
                    [
                        'permanent' => 'DESC',
                        'startDate' => 'DESC',
                        'id' => 'DESC',
                    ],
                ),
            ],
        );
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        JamRepository $repository,
        EntityManagerInterface $em,
    ): Response {
        $jam = (new Jam())
            ->setEnabled(true)
            ->setMode(Jam::MODE_RANDOM)
            ->setCreatedAt(
                new \DateTimeImmutable(),
            );

        return $this->handleForm(
            $request,
            $jam,
            $repository,
            $em,
            'Nouvelle Jam',
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
        Jam $jam,
        JamRepository $repository,
        EntityManagerInterface $em,
    ): Response {
        return $this->handleForm(
            $request,
            $jam,
            $repository,
            $em,
            'Modifier la Jam',
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
        Jam $jam,
        EntityManagerInterface $em,
    ): Response {
        if (!$this->isCsrfTokenValid(
            'delete-jam-' . $jam->getId(),
            $request->getPayload()->getString('_token'),
        )) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($jam);
        $em->flush();

        $this->addFlash(
            'success',
            'Jam supprimée.',
        );

        return $this->redirectToRoute(
            'admin_jam_index',
        );
    }

    private function handleForm(
        Request $request,
        Jam $jam,
        JamRepository $repository,
        EntityManagerInterface $em,
        string $title,
    ): Response {
        $form = $this->createForm(
            JamType::class,
            $jam,
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->validateBusinessRules(
                $form,
                $jam,
                $repository,
            );

            if ($form->isValid()) {
                foreach ($jam->getScheduleDays() as $day) {
                    if ($day->getTheme() !== null) {
                        $day->setThemeLabel(
                            (string) $day
                                ->getTheme()
                                ->getName(),
                        );
                    }
                }

                $em->persist($jam);
                $em->flush();

                $this->addFlash(
                    'success',
                    'Jam enregistrée.',
                );

                return $this->redirectToRoute(
                    'admin_jam_index',
                );
            }
        }

        return $this->render(
            'admin/jam/form.html.twig',
            [
                'form' => $form,
                'title' => $title,
                'jam' => $jam,
            ],
        );
    }

    private function validateBusinessRules(
        FormInterface $form,
        Jam $jam,
        JamRepository $repository,
    ): void {
        if ($jam->isPermanent()) {
            if ($jam->getMode() !== Jam::MODE_RANDOM) {
                $form->get('mode')->addError(
                    new FormError(
                        'La Jam permanente doit être en mode aléatoire.',
                    ),
                );
            }

            if (
                $repository->findOtherPermanent(
                    $jam->getId(),
                ) !== null
            ) {
                $form->get('permanent')->addError(
                    new FormError(
                        'Une Jam permanente existe déjà.',
                    ),
                );
            }

            return;
        }

        $start = $jam->getStartDate();
        $end = $jam->getEndDate();

        if ($start === null) {
            $form->get('startDate')->addError(
                new FormError(
                    'Une Jam spéciale doit avoir une date de début.',
                ),
            );
        }

        if ($end === null) {
            $form->get('endDate')->addError(
                new FormError(
                    'Une Jam spéciale doit avoir une date de fin.',
                ),
            );
        }

        if ($start === null || $end === null) {
            return;
        }

        if ($start > $end) {
            $form->get('endDate')->addError(
                new FormError(
                    'La fin doit être postérieure ou égale au début.',
                ),
            );

            return;
        }

        if (
            $jam->isEnabled()
            && $repository->findOverlappingSpecial(
                $start,
                $end,
                $jam->getId(),
            ) !== null
        ) {
            $form->get('startDate')->addError(
                new FormError(
                    'Cette période chevauche une autre Jam spéciale active.',
                ),
            );
        }

        if ($jam->getMode() !== Jam::MODE_FIXED) {
            return;
        }

        $expected = [];

        for (
            $date = $start;
            $date <= $end;
            $date = $date->modify('+1 day')
        ) {
            $expected[$date->format('Y-m-d')] = true;
        }

        $actual = [];

        foreach ($jam->getScheduleDays() as $day) {
            $date = $day->getDate();

            if ($date === null) {
                continue;
            }

            $key = $date->format('Y-m-d');

            if (isset($actual[$key])) {
                $form->get('scheduleDays')->addError(
                    new FormError(
                        'Une date est présente plusieurs fois.',
                    ),
                );

                return;
            }

            $actual[$key] = true;
        }

        if (
            array_keys($expected)
            !== array_keys($actual)
        ) {
            $form->get('scheduleDays')->addError(
                new FormError(
                    'Le mode fixe doit contenir exactement une configuration pour chaque jour de la période.',
                ),
            );
        }
    }
}
