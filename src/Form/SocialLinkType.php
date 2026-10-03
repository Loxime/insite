<?php

namespace App\Form;

use App\Entity\SocialLink;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SocialLinkType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add('platform', ChoiceType::class, [
                'label' => 'Plateforme',
                'choices' => [
                    'Discord' => 'discord',
                    'GitHub' => 'github',
                    'Instagram' => 'instagram',
                    'LinkedIn' => 'linkedin',
                    'TikTok' => 'tiktok',
                    'Twitch' => 'twitch',
                    'X / Twitter' => 'x-twitter',
                    'YouTube' => 'youtube',
                    'Bluesky' => 'bluesky',
                    'Site web' => 'website',
                ],
            ])
            ->add('label', TextType::class, [
                'label' => 'Libellé',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Optionnel',
                ],
            ])
            ->add('url', UrlType::class, [
                'label' => 'URL',
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'Afficher',
                'required' => false,
            ])
            ->add(
                'displayOrder',
                HiddenType::class,
            );
    }

    public function configureOptions(
        OptionsResolver $resolver,
    ): void {
        $resolver->setDefaults([
            'data_class' => SocialLink::class,
        ]);
    }
}
