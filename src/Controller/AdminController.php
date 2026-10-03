<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminPasswordType;
use App\Form\AdminProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class AdminController extends AbstractController
{
    #[Route('/admin/login', name: 'admin_login', methods: ['GET', 'POST'])]
    public function login(
        AuthenticationUtils $authenticationUtils,
    ): Response {
        if ($this->getUser() instanceof User) {
            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/login.html.twig', [
            'last_email' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/admin', name: 'admin', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $profileForm = $this->createForm(
            AdminProfileType::class,
            $user,
        );

        $profileForm->handleRequest($request);

        if (
            $profileForm->isSubmitted()
            && $profileForm->isValid()
        ) {
            $em->flush();

            $this->addFlash(
                'success',
                'Username mis à jour.',
            );

            return $this->redirectToRoute('admin');
        }

        $passwordForm = $this->createForm(
            AdminPasswordType::class,
        );

        $passwordForm->handleRequest($request);

        if (
            $passwordForm->isSubmitted()
            && $passwordForm->isValid()
        ) {
            $currentPassword = (string) $passwordForm
                ->get('currentPassword')
                ->getData();

            $newPassword = (string) $passwordForm
                ->get('newPassword')
                ->getData();

            $confirmPassword = (string) $passwordForm
                ->get('confirmPassword')
                ->getData();

            if (!$hasher->isPasswordValid(
                $user,
                $currentPassword,
            )) {
                $passwordForm
                    ->get('currentPassword')
                    ->addError(
                        new FormError(
                            'Mot de passe actuel incorrect.',
                        ),
                    );
            } elseif ($newPassword !== $confirmPassword) {
                $passwordForm
                    ->get('confirmPassword')
                    ->addError(
                        new FormError(
                            'Les mots de passe ne correspondent pas.',
                        ),
                    );
            } elseif (mb_strlen($newPassword) < 8) {
                $passwordForm
                    ->get('newPassword')
                    ->addError(
                        new FormError(
                            'Le mot de passe doit contenir au moins 8 caractères.',
                        ),
                    );
            } else {
                $user->setPassword(
                    $hasher->hashPassword(
                        $user,
                        $newPassword,
                    ),
                );

                $em->flush();

                $this->addFlash(
                    'success',
                    'Mot de passe modifié.',
                );

                return $this->redirectToRoute('admin');
            }
        }

        return $this->render('admin/index.html.twig', [
            'profile_form' => $profileForm,
            'password_form' => $passwordForm,
        ]);
    }

    #[Route('/admin/logout', name: 'admin_logout', methods: ['GET'])]
    public function logout(): never
    {
        throw new \LogicException(
            'Cette route est interceptée par le firewall.',
        );
    }
}
