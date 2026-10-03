<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ContactType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse mail',
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                ],
            ])
            ->add('subject', TextType::class, [
                'label' => 'Objet',
                'constraints' => [
                    new NotBlank(),
                    new Length(max: 200),
                ],
            ])
            ->add('body', TextareaType::class, [
                'label' => 'Message',
                'attr' => ['rows' => 12],
                'constraints' => [
                    new NotBlank(),
                    new Length(max: 10000),
                ],
            ]);
    }
}
