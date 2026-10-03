<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact', methods: ['GET', 'POST'])]
    public function contact(
        Request $request,
        MailerInterface $mailer,
        #[Autowire('%env(CONTACT_RECIPIENT)%')]
        string $recipient,
        #[Autowire('%env(CONTACT_FROM)%')]
        string $from,
    ): Response {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $message = (new Email())
                ->from($from)
                ->to($recipient)
                ->replyTo($data['email'])
                ->subject('[INQUEST] ' . $data['subject'])
                ->text(
                    "Adresse de réponse : {$data['email']}\n\n"
                    . $data['body']
                );

            $mailer->send($message);

            $this->addFlash(
                'success',
                'Votre message a bien été envoyé.',
            );

            return $this->redirectToRoute('contact');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form,
        ]);
    }
}
