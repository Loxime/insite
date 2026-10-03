<?php

namespace App\Form;

use App\Entity\Game;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;

final class GameType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('summary', TextareaType::class, [
                'label' => 'Résumé court',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'maxlength' => 500,
                    'placeholder' => (
                        'Courte présentation du jeu'
                    ),
                ],
            ])
            ->add('displayOrder', IntegerType::class, [
                'label' => 'Ordre d’affichage',
                'required' => true,
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Brouillon' => 'draft',
                    'Publié' => 'published',
                    'Archivé' => 'archived',
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image principale',
                'mapped' => false,
                'required' => false,
                'help' => (
                    'JPEG, PNG ou WebP. 5 Mo maximum.'
                ),
                'constraints' => [
                    new Image(
                        maxSize: '5M',
                        mimeTypes: [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        maxSizeMessage: (
                            'L’image ne doit pas dépasser 5 Mo.'
                        ),
                        mimeTypesMessage: (
                            'Utilise une image JPEG, PNG ou WebP.'
                        ),
                    ),
                ],
            ])
            ->add('links', CollectionType::class, [
                'entry_type' => GameLinkType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'constraints' => [
                    new NotBlank(
                        message: (
                            'La description du jeu est obligatoire.'
                        ),
                    ),
                ],
                'attr' => [
                    'class' => 'js-ckeditor',
                    'rows' => 18,
                ],
            ])
            ->add('sections', CollectionType::class, [
                'entry_type' => GameSectionType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
            ]);

        if ($options['show_remove_image']) {
            $builder->add(
                'removeImage',
                CheckboxType::class,
                [
                    'label' => (
                        'Supprimer l’image actuelle'
                    ),
                    'mapped' => false,
                    'required' => false,
                ],
            );
        }
    }

    public function configureOptions(
        OptionsResolver $resolver,
    ): void {
        $resolver->setDefaults([
            'data_class' => Game::class,
            'show_remove_image' => false,
        ]);

        $resolver->setAllowedTypes(
            'show_remove_image',
            'bool',
        );
    }
}
