<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\TemporaryAnnouncement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class TemporaryAnnouncementType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add('buttonLabel', TextType::class, [
                'label' => 'Texte du bouton',
                'attr' => ['placeholder' => 'Découvrir le jeu'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(max: 120),
                ],
            ])
            ->add('buttonUrl', TextType::class, [
                'label' => 'Lien de redirection',
                'attr' => ['placeholder' => '/games ou https://example.com'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(max: 2048),
                    new Assert\Regex(
                        pattern: '~^(?:/(?!/)[^\s]*|https?://[^\s]+)$~i',
                        message: 'Saisis un chemin interne ou une URL HTTP(S).',
                    ),
                ],
            ])
            ->add('startsAt', DateTimeType::class, [
                'label' => 'Début de publication (heure de Paris)',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'model_timezone' => 'UTC',
                'view_timezone' => 'Europe/Paris',
                'required' => false,
            ])
            ->add('endsAt', DateTimeType::class, [
                'label' => 'Fin de publication (heure de Paris)',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'model_timezone' => 'UTC',
                'view_timezone' => 'Europe/Paris',
                'required' => false,
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'Annonce activée',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TemporaryAnnouncement::class,
        ]);
    }
}
