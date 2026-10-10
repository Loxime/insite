<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class JamPlatformType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Plateforme',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 80)],
            ])
            ->add('url', UrlType::class, [
                'label' => 'URL de publication',
                'constraints' => [new Assert\NotBlank(), new Assert\Url(protocols: ['http', 'https'])],
            ])
            ->add('icon', ChoiceType::class, [
                'label' => 'Logo',
                'choices' => [
                    'Steam' => 'steam',
                    'itch.io' => 'itch-io',
                    'Epic Games' => 'epic-games',
                    'Xbox' => 'xbox',
                    'PlayStation' => 'playstation',
                    'Autre plateforme' => 'link',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => null]);
    }
}
