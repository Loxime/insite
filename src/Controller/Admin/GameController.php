<?php

namespace App\Controller\Admin;

use App\Entity\Game;
use App\Entity\GameSection;
use App\Form\GameType;
use App\Repository\GameRepository;
use App\Service\GameSlugger;
use App\Service\MinioStorage;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/games', name: 'admin_games_')]
final class GameController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        GameRepository $gameRepository,
        PaginatorInterface $paginator,
    ): Response {
        $search = trim(
            $request->query->getString('q'),
        );

        $games = $paginator->paginate(
            $gameRepository->createAdminListQuery(
                $search,
            ),
            max(
                1,
                $request->query->getInt(
                    'page',
                    1,
                ),
            ),
            20,
        );

        return $this->render(
            'admin/games/index.html.twig',
            [
                'games' => $games,
                'search' => $search,
            ],
        );
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        GameSlugger $slugger,
        MinioStorage $storage,
    ): Response {
        $game = new Game();

        $game
            ->setCreatedAt(new \DateTimeImmutable())
            ->setStatus('draft')
            ->setDisplayOrder(0);

        $form = $this->createForm(
            GameType::class,
            $game,
        );

        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
        ) {
            $game->setSlug(
                $slugger->generate(
                    $game->getTitle() ?? '',
                ),
            );

            $image = $form
                ->get('image')
                ->getData();

            if ($image instanceof UploadedFile) {
                $game->setImageKey(
                    $storage->uploadGameImage(
                        $image,
                    ),
                );
            }

            $this->processSectionImages(
                $form,
                $storage,
            );

            $entityManager->persist($game);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Jeu créé avec succès.',
            );

            return $this->redirectToRoute(
                'admin_games_index',
                [],
                Response::HTTP_SEE_OTHER,
            );
        }

        return $this->render(
            'admin/games/new.html.twig',
            [
                'game' => $game,
                'form' => $form,
            ],
        );
    }

    #[Route(
        '/{id}/edit',
        name: 'edit',
        methods: ['GET', 'POST'],
    )]
    public function edit(
        Request $request,
        Game $game,
        EntityManagerInterface $entityManager,
        GameSlugger $slugger,
        MinioStorage $storage,
    ): Response {
        $previousImageKey = $game->getImageKey();

        $previousSectionImages = [];

        foreach ($game->getSections() as $section) {
            if ($section->getId() !== null) {
                $previousSectionImages[
                    $section->getId()
                ] = $section->getImageKey();
            }
        }

        $form = $this->createForm(
            GameType::class,
            $game,
            [
                'show_remove_image' => (
                    $previousImageKey !== null
                ),
            ],
        );

        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
        ) {
            $game->setSlug(
                $slugger->generate(
                    $game->getTitle() ?? '',
                    $game,
                ),
            );

            $imagesToDelete = [];

            $image = $form
                ->get('image')
                ->getData();

            $removeImage = (
                $form->has('removeImage')
                && (bool) $form
                    ->get('removeImage')
                    ->getData()
            );

            if ($image instanceof UploadedFile) {
                $game->setImageKey(
                    $storage->uploadGameImage(
                        $image,
                    ),
                );

                if ($previousImageKey !== null) {
                    $imagesToDelete[] = (
                        $previousImageKey
                    );
                }
            } elseif (
                $removeImage
                && $previousImageKey !== null
            ) {
                $game->setImageKey(null);

                $imagesToDelete[] = (
                    $previousImageKey
                );
            }

            $sectionImagesToDelete = (
                $this->processSectionImages(
                    $form,
                    $storage,
                    $previousSectionImages,
                )
            );

            $imagesToDelete = array_merge(
                $imagesToDelete,
                $sectionImagesToDelete,
            );

            $entityManager->flush();

            $this->deleteImages(
                $storage,
                $imagesToDelete,
            );

            $this->addFlash(
                'success',
                'Jeu modifié avec succès.',
            );

            return $this->redirectToRoute(
                'admin_games_index',
                [],
                Response::HTTP_SEE_OTHER,
            );
        }

        return $this->render(
            'admin/games/edit.html.twig',
            [
                'game' => $game,
                'form' => $form,
            ],
        );
    }

    #[Route(
        '/{id}/status/{status}',
        name: 'status',
        methods: ['POST'],
        requirements: [
            'status' => 'draft|published|archived',
        ],
    )]
    public function status(
        Request $request,
        Game $game,
        string $status,
        EntityManagerInterface $entityManager,
    ): Response {
        $token = sprintf(
            'game-status-%d-%s',
            $game->getId(),
            $status,
        );

        if (
            !$this->isCsrfTokenValid(
                $token,
                $request
                    ->getPayload()
                    ->getString('_token'),
            )
        ) {
            throw $this
                ->createAccessDeniedException();
        }

        $game->setStatus($status);

        $entityManager->flush();

        $this->addFlash(
            'success',
            match ($status) {
                'published' => 'Jeu publié.',
                'archived' => 'Jeu archivé.',
                default => (
                    'Jeu enregistré comme brouillon.'
                ),
            },
        );

        return $this->redirectToRoute(
            'admin_games_index',
        );
    }

    #[Route(
        '/{id}/delete',
        name: 'delete',
        methods: ['POST'],
    )]
    public function delete(
        Request $request,
        Game $game,
        EntityManagerInterface $entityManager,
        MinioStorage $storage,
    ): Response {
        if (
            !$this->isCsrfTokenValid(
                'delete-game-' . $game->getId(),
                $request
                    ->getPayload()
                    ->getString('_token'),
            )
        ) {
            throw $this
                ->createAccessDeniedException();
        }

        $images = [];

        if ($game->getImageKey() !== null) {
            $images[] = $game->getImageKey();
        }

        foreach ($game->getSections() as $section) {
            if ($section->getImageKey() !== null) {
                $images[] = $section->getImageKey();
            }
        }

        $entityManager->remove($game);
        $entityManager->flush();

        $this->deleteImages(
            $storage,
            $images,
        );

        $this->addFlash(
            'success',
            'Jeu supprimé.',
        );

        return $this->redirectToRoute(
            'admin_games_index',
        );
    }

    /**
     * @param array<int, string|null> $previousImages
     *
     * @return list<string>
     */
    private function processSectionImages(
        FormInterface $form,
        MinioStorage $storage,
        array $previousImages = [],
    ): array {
        $imagesToDelete = [];
        $currentIds = [];

        $sectionsForm = $form->get('sections');

        foreach ($sectionsForm as $sectionForm) {
            $section = $sectionForm->getData();

            if (!$section instanceof GameSection) {
                continue;
            }

            if ($section->getId() !== null) {
                $currentIds[] = $section->getId();
            }

            $previousImage = (
                $section->getId() !== null
                ? (
                    $previousImages[
                        $section->getId()
                    ] ?? null
                )
                : null
            );

            $image = $sectionForm
                ->get('image')
                ->getData();

            $removeImage = (
                $sectionForm->has('removeImage')
                && (bool) $sectionForm
                    ->get('removeImage')
                    ->getData()
            );

            if ($image instanceof UploadedFile) {
                $section->setImageKey(
                    $storage
                        ->uploadGameSectionImage(
                            $image,
                        ),
                );

                if ($previousImage !== null) {
                    $imagesToDelete[] = (
                        $previousImage
                    );
                }
            } elseif (
                $removeImage
                && $previousImage !== null
            ) {
                $section->setImageKey(null);

                $imagesToDelete[] = (
                    $previousImage
                );
            }
        }

        foreach (
            $previousImages
            as $sectionId => $imageKey
        ) {
            if (
                $imageKey !== null
                && !in_array(
                    $sectionId,
                    $currentIds,
                    true,
                )
            ) {
                $imagesToDelete[] = $imageKey;
            }
        }

        return array_values(
            array_unique(
                $imagesToDelete,
            ),
        );
    }

    /**
     * @param list<string> $images
     */
    private function deleteImages(
        MinioStorage $storage,
        array $images,
    ): void {
        foreach (
            array_unique($images)
            as $image
        ) {
            $storage->delete($image);
        }
    }
}
