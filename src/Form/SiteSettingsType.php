<?php

namespace App\Form;

use App\Entity\SiteSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SiteSettingsType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add('brandText', TextType::class, [
                'label' => 'Nom du site',
            ])
            ->add('blogLabel', TextType::class, [
                'label' => 'Nom de l’onglet Blog',
            ])
            ->add('blogEnabled', CheckboxType::class, [
                'label' => 'Afficher Blog',
                'required' => false,
            ])
            ->add('gamesLabel', TextType::class, [
                'label' => 'Nom de l’onglet Games',
            ])
            ->add('gamesEnabled', CheckboxType::class, [
                'label' => 'Afficher Games',
                'required' => false,
            ])
            ->add('aboutLabel', TextType::class, [
                'label' => 'Nom de l’onglet About',
            ])
            ->add('aboutEnabled', CheckboxType::class, [
                'label' => 'Afficher About',
                'required' => false,
            ])
            ->add('footerText', TextareaType::class, [
                'label' => 'Texte du footer',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Texte affiché dans le pied de page',
                ],
            ])
            ->add('socialLinks', CollectionType::class, [
                'entry_type' => SiteSocialLinkType::class,
                'entry_options' => ['label' => false],
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
            ]);
    }

    public function configureOptions(
        OptionsResolver $resolver,
    ): void {
        $resolver->setDefaults([
            'data_class' => SiteSettings::class,
        ]);
    }
}
