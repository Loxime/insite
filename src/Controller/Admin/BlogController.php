<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Service\MinioStorage;
use App\Service\PostSlugger;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/blog', name: 'admin_blog_')]
final class BlogController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        PostRepository $postRepository,
        PaginatorInterface $paginator,
    ): Response {
        $search = trim($request->query->getString('q'));

        $pagination = $paginator->paginate(
            $postRepository->createAdminListQuery($search),
            max(1, $request->query->getInt('page', 1)),
            20,
        );

        return $this->render('admin/blog/index.html.twig', [
            'posts' => $pagination,
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        PostSlugger $slugger,
        MinioStorage $storage,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $post = new Post();

        $post
            ->setCreatedAt(new \DateTimeImmutable())
            ->setAuthor($user)
            ->setStatus('draft');

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setSlug(
                $slugger->generate($post->getTitle() ?? ''),
            );

            $image = $form->get('image')->getData();

            if ($image instanceof UploadedFile) {
                $post->setImageKey(
                    $storage->uploadBlogImage($image),
                );
            }

            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Article créé avec succès.',
            );

            return $this->redirectToRoute(
                'admin_blog_index',
                [],
                Response::HTTP_SEE_OTHER,
            );
        }

        return $this->render('admin/blog/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Post $post,
        EntityManagerInterface $entityManager,
        PostSlugger $slugger,
        MinioStorage $storage,
    ): Response {
        $previousImageKey = $post->getImageKey();

        $form = $this->createForm(
            PostType::class,
            $post,
            [
                'show_remove_image' => $post->getImageKey() !== null,
            ],
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setSlug(
                $slugger->generate(
                    $post->getTitle() ?? '',
                    $post,
                ),
            );

            $image = $form->get('image')->getData();

            $removeImage = (
                $form->has('removeImage')
                && (bool) $form->get('removeImage')->getData()
            );

            if ($image instanceof UploadedFile) {
                $post->setImageKey(
                    $storage->uploadBlogImage($image),
                );

                $entityManager->flush();

                if ($previousImageKey !== null) {
                    $storage->delete($previousImageKey);
                }
            } elseif (
                $removeImage
                && $previousImageKey !== null
            ) {
                $post->setImageKey(null);

                $entityManager->flush();

                $storage->delete($previousImageKey);
            } else {
                $entityManager->flush();
            }

            $this->addFlash(
                'success',
                'Article modifié avec succès.',
            );

            return $this->redirectToRoute(
                'admin_blog_index',
                [],
                Response::HTTP_SEE_OTHER,
            );
        }

        return $this->render('admin/blog/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
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
        Post $post,
        string $status,
        EntityManagerInterface $entityManager,
    ): Response {
        if (!$this->isCsrfTokenValid(
            'post-status-' . $post->getId() . '-' . $status,
            $request->getPayload()->getString('_token'),
        )) {
            throw $this->createAccessDeniedException();
        }

        $post->setStatus($status);
        $entityManager->flush();

        $this->addFlash(
            'success',
            match ($status) {
                'published' => 'Article publié.',
                'archived' => 'Article archivé.',
                default => 'Article enregistré comme brouillon.',
            },
        );

        return $this->redirectToRoute('admin_blog_index');
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Post $post,
        EntityManagerInterface $entityManager,
        MinioStorage $storage,
    ): Response {
        if (!$this->isCsrfTokenValid(
            'delete-post-' . $post->getId(),
            $request->getPayload()->getString('_token'),
        )) {
            throw $this->createAccessDeniedException();
        }

        $imageKey = $post->getImageKey();

        $entityManager->remove($post);
        $entityManager->flush();

        $storage->delete($imageKey);

        $this->addFlash(
            'success',
            'Article supprimé.',
        );

        return $this->redirectToRoute('admin_blog_index');
    }
}
