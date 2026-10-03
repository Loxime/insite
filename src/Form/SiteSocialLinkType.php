<?php

namespace App\Form;

use App\Entity\SiteSocialLink;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SiteSocialLinkType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add('platform', ChoiceType::class, [
                'label' => 'Réseau',
                'choices' => [
                    'TikTok' => 'tiktok',
                    'YouTube' => 'youtube',
                    'Discord' => 'discord',
                    'Facebook' => 'facebook',
                    'Instagram' => 'instagram',
                    'X / Twitter' => 'x-twitter',
                    'Twitch' => 'twitch',
                    'GitHub' => 'github',
                    'LinkedIn' => 'linkedin',
                    'Bluesky' => 'bluesky',
                ],
            ])
            ->add('url', UrlType::class, [
                'label' => 'Lien',
            ])
            ->add('displayOrder', IntegerType::class, [
                'label' => 'Ordre',
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'Afficher',
                'required' => false,
            ]);
    }

    public function configureOptions(
        OptionsResolver $resolver,
    ): void {
        $resolver->setDefaults([
            'data_class' => SiteSocialLink::class,
        ]);
    }
}
