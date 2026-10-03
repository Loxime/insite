<?php

namespace App\Controller\Admin;

use App\Entity\About;
use App\Form\AboutType;
use App\Repository\AboutRepository;
use App\Service\MinioStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AboutController extends AbstractController
{
    #[Route(
        '/admin/about',
        name: 'admin_about_edit',
        methods: ['GET', 'POST'],
    )]
    public function edit(
        Request $request,
        AboutRepository $aboutRepository,
        EntityManagerInterface $entityManager,
        MinioStorage $storage,
    ): Response {
        $about = $aboutRepository->findContent();

        if ($about === null) {
            $about = new About();

            $about->setUpdatedAt(
                new \DateTimeImmutable(),
            );

            $entityManager->persist($about);
        }

        $previousImageKey = $about->getImageKey();

        $form = $this->createForm(
            AboutType::class,
            $about,
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
            $image = $form
                ->get('image')
                ->getData();

            $removeImage = (
                $form->has('removeImage')
                && (bool) $form
                    ->get('removeImage')
                    ->getData()
            );

            $imageToDelete = null;

            if ($image instanceof UploadedFile) {
                $about->setImageKey(
                    $storage->uploadAboutImage(
                        $image,
                    ),
                );

                $imageToDelete = $previousImageKey;
            } elseif (
                $removeImage
                && $previousImageKey !== null
            ) {
                $about->setImageKey(null);
                $imageToDelete = $previousImageKey;
            }

            $about->setUpdatedAt(
                new \DateTimeImmutable(),
            );

            $entityManager->persist($about);
            $entityManager->flush();

            if ($imageToDelete !== null) {
                $storage->delete($imageToDelete);
            }

            $this->addFlash(
                'success',
                'Présentation mise à jour.',
            );

            return $this->redirectToRoute(
                'admin_about_edit',
            );
        }

        return $this->render(
            'admin/about/edit.html.twig',
            [
                'about' => $about,
                'form' => $form,
            ],
        );
    }
}
