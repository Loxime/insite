<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\TemporaryAnnouncement;
use App\Form\TemporaryAnnouncementType;
use App\Repository\TemporaryAnnouncementRepository;
use App\Service\MinioStorage;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
        Request $request,
        TemporaryAnnouncementRepository $repository,
        PaginatorInterface $paginator,
    ): Response {
        $search = mb_substr(trim($request->query->getString('q')), 0, 120);

        $query = $repository->createQueryBuilder('announcement')
            ->orderBy('announcement.createdAt', 'DESC')
            ->addOrderBy('announcement.id', 'DESC');

        if ($search !== '') {
            $fields = ['announcement.bannerText', 'announcement.buttonLabel', 'announcement.buttonUrl'];
            $conditions = array_map(
                static fn (string $field): string => 'LOWER(' . $field . ') LIKE :search',
                $fields,
            );

            $query->andWhere(implode(' OR ', $conditions))
                ->setParameter('search', '%' . mb_strtolower($search) . '%');
        }

        return $this->render('admin/announcements/index.html.twig', [
            'announcements' => $paginator->paginate(
                $query,
                max(1, $request->query->getInt('page', 1)),
                10,
            ),
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        MinioStorage $storage,
    ): Response {
        $announcement = new TemporaryAnnouncement();

        $form = $this->createForm(
            TemporaryAnnouncementType::class,
            $announcement,
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->validateSchedule($form, $announcement);
            $this->validateContent($form, $announcement);

            if ($form->isValid()) {
                $image = $form->get('image')->getData();
                $uploadedKey = null;

                if ($image instanceof UploadedFile) {
                    $uploadedKey = $storage->uploadAnnouncementImage($image);
                    $announcement->setImageKey($uploadedKey);
                }

                try {
                    $entityManager->persist($announcement);
                    $entityManager->flush();
                } catch (\Throwable $exception) {
                    if ($uploadedKey !== null) {
                        $storage->delete($uploadedKey);
                    }

                    throw $exception;
                }

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
        MinioStorage $storage,
    ): Response {
        $form = $this->createForm(
            TemporaryAnnouncementType::class,
            $announcement,
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->validateSchedule($form, $announcement);
            $this->validateContent($form, $announcement);

            if ($form->isValid()) {
                $previousKey = $announcement->getImageKey();
                $image = $form->get('image')->getData();
                $removeImage = (bool) $form->get('removeImage')->getData();

                $uploadedKey = null;
                $keyToDelete = null;

                if ($image instanceof UploadedFile) {
                    $uploadedKey = $storage->uploadAnnouncementImage($image);
                    $announcement->setImageKey($uploadedKey);
                    $keyToDelete = $previousKey;
                } elseif ($removeImage && $previousKey !== null) {
                    $announcement->setImageKey(null);
                    $keyToDelete = $previousKey;
                }

                $announcement->touchUpdatedAt();

                try {
                    $entityManager->flush();
                } catch (\Throwable $exception) {
                    if ($uploadedKey !== null) {
                        $storage->delete($uploadedKey);
                    }

                    throw $exception;
                }

                if ($keyToDelete !== null) {
                    $storage->delete($keyToDelete);
                }

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
        MinioStorage $storage,
    ): Response {
        if (!$this->isCsrfTokenValid(
            'delete-announcement-' . $announcement->getId(),
            $request->getPayload()->getString('_token'),
        )) {
            throw $this->createAccessDeniedException();
        }

        $imageKey = $announcement->getImageKey();

        $entityManager->remove($announcement);
        $entityManager->flush();

        $storage->delete($imageKey);

        $this->addFlash('success', 'Annonce supprimée.');

        return $this->redirectToRoute('admin_announcements_index');
    }

    private function validateContent(
        FormInterface $form,
        TemporaryAnnouncement $announcement,
    ): void {
        if ($announcement->getType() === TemporaryAnnouncement::TYPE_BANNER) {
            if (trim((string) $announcement->getBannerText()) === '') {
                $form->get('bannerText')->addError(
                    new FormError('Le texte du bandeau est obligatoire.'),
                );
            }

            return;
        }

        if (trim($announcement->getButtonLabel()) === '') {
            $form->get('buttonLabel')->addError(
                new FormError('Le texte du bouton est obligatoire.'),
            );
        }

        $image = $form->get('image')->getData();
        $removeImage = (bool) $form->get('removeImage')->getData();

        if (
            $announcement->isEnabled()
            && !($image instanceof UploadedFile)
            && (
                $announcement->getImageKey() === null
                || $removeImage
            )
        ) {
            $form->get('image')->addError(
                new FormError('Une popup activée doit posséder une image.'),
            );
        }
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
