<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\AboutProfile;
use App\Form\AboutProfileType;
use App\Repository\AboutProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/about/profiles', name: 'admin_about_profiles_')]
final class AboutProfileController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        AboutProfileRepository $repository,
    ): Response {
        return $this->render('admin/about/profiles/index.html.twig', [
            'profiles' => $repository->findBy(
                [],
                ['displayOrder' => 'ASC', 'id' => 'ASC'],
            ),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        AboutProfileRepository $repository,
        SluggerInterface $slugger,
    ): Response {
        $profile = new AboutProfile();
        $form = $this->createProfileForm($profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->prepareProfile($form, $profile, $repository, $slugger);

            if ($form->isValid()) {
                $entityManager->persist($profile);
                $entityManager->flush();

                $this->addFlash('success', 'Profil créé.');

                return $this->redirectToRoute(
                    'admin_about_profiles_index',
                );
            }
        }

        return $this->render('admin/about/profiles/form.html.twig', [
            'form' => $form,
            'profile' => $profile,
            'heading' => 'Nouveau profil',
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
        AboutProfile $profile,
        EntityManagerInterface $entityManager,
        AboutProfileRepository $repository,
        SluggerInterface $slugger,
    ): Response {
        $form = $this->createProfileForm($profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->prepareProfile($form, $profile, $repository, $slugger);

            if ($form->isValid()) {
                $profile->touchUpdatedAt();
                $entityManager->flush();

                $this->addFlash('success', 'Profil mis à jour.');

                return $this->redirectToRoute(
                    'admin_about_profiles_index',
                );
            }
        }

        return $this->render('admin/about/profiles/form.html.twig', [
            'form' => $form,
            'profile' => $profile,
            'heading' => 'Modifier le profil',
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
        AboutProfile $profile,
        EntityManagerInterface $entityManager,
    ): Response {
        if (!$this->isCsrfTokenValid(
            'delete-about-profile-' . $profile->getId(),
            $request->getPayload()->getString('_token'),
        )) {
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($profile);
        $entityManager->flush();

        $this->addFlash('success', 'Profil supprimé.');

        return $this->redirectToRoute('admin_about_profiles_index');
    }

    private function createProfileForm(
        AboutProfile $profile,
    ): FormInterface {
        return $this->createForm(AboutProfileType::class, $profile, [
            'languages_data' => implode("\n", $profile->getLanguages()),
            'skills_data' => implode("\n", $profile->getSkills()),
        ]);
    }

    private function prepareProfile(
        FormInterface $form,
        AboutProfile $profile,
        AboutProfileRepository $repository,
        SluggerInterface $slugger,
    ): void {
        $requestedSlug = trim($profile->getSlug());

        $slug = $slugger
            ->slug(
                $requestedSlug !== ''
                    ? $requestedSlug
                    : $profile->getDisplayName(),
            )
            ->lower()
            ->toString();

        if ($slug === '' || strlen($slug) > 220) {
            $form->get('slug')->addError(
                new FormError('Identifiant URL invalide.'),
            );

            return;
        }

        $existing = $repository->findOneBy(['slug' => $slug]);

        if (
            $existing !== null
            && $existing->getId() !== $profile->getId()
        ) {
            $form->get('slug')->addError(
                new FormError('Cet identifiant URL est déjà utilisé.'),
            );

            return;
        }

        $languages = $this->parseList(
            (string) $form->get('languagesInput')->getData(),
        );

        $skills = $this->parseList(
            (string) $form->get('skillsInput')->getData(),
        );

        if (count($languages) > 30 || count($skills) > 30) {
            $form->addError(
                new FormError('Maximum 30 éléments par liste.'),
            );

            return;
        }

        foreach ([$languages, $skills] as $items) {
            foreach ($items as $item) {
                if (mb_strlen($item) > 100) {
                    $form->addError(
                        new FormError(
                            'Chaque langue ou compétence est limitée à 100 caractères.',
                        ),
                    );

                    return;
                }
            }
        }

        $profile->setSlug($slug);
        $profile->setLanguages($languages);
        $profile->setSkills($skills);
    }

    /**
     * @return list<string>
     */
    private function parseList(string $value): array
    {
        $items = preg_split('/[,;\r\n]+/u', $value) ?: [];

        $items = array_map('trim', $items);

        $items = array_filter(
            $items,
            static fn (string $item): bool => $item !== '',
        );

        return array_values(array_unique($items));
    }
}
