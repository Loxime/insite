<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\AdminForcePasswordType;
use App\Form\AdminUserCreateType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER_ADMIN')]
#[Route('/admin/users', name: 'admin_users_')]
final class AdminUserController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(UserRepository $repository): Response
    {
        return $this->render('admin/users/index.html.twig', [
            'users' => $repository->findBy([], ['email' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        UserRepository $repository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
    ): Response {
        $form = $this->createForm(AdminUserCreateType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = trim((string) $form->get('email')->getData());
            $password = (string) $form->get('password')->getData();

            if ($repository->findOneBy(['email' => $email])) {
                $form->get('email')->addError(
                    new FormError('Cet email existe déjà.'),
                );
            } elseif (mb_strlen($password) < 8) {
                $form->get('password')->addError(
                    new FormError('8 caractères minimum.'),
                );
            } else {
                $user = new User();

                $user
                    ->setEmail($email)
                    ->setUsername($form->get('username')->getData())
                    ->setRole($form->get('role')->getData())
                    ->setCreatedAt(new \DateTimeImmutable());

                $user->setPassword(
                    $hasher->hashPassword($user, $password),
                );

                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('admin_users_index');
            }
        }

        return $this->render('admin/users/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/password', name: 'password', methods: ['GET', 'POST'])]
    public function password(
        Request $request,
        User $user,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
    ): Response {
        $form = $this->createForm(AdminForcePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = (string) $form->get('password')->getData();
            $confirm = (string) $form->get('confirmPassword')->getData();

            if ($password !== $confirm) {
                $form->get('confirmPassword')->addError(
                    new FormError('Les mots de passe diffèrent.'),
                );
            } elseif (mb_strlen($password) < 8) {
                $form->get('password')->addError(
                    new FormError('8 caractères minimum.'),
                );
            } else {
                $user->setPassword(
                    $hasher->hashPassword($user, $password),
                );

                $em->flush();

                return $this->redirectToRoute('admin_users_index');
            }
        }

        return $this->render('admin/users/password.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
}
