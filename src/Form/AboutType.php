<?php

namespace App\Form;

use App\Entity\About;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

final class AboutType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('image', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => false,
                'help' => 'JPEG, PNG ou WebP. 5 Mo maximum.',
                'constraints' => [
                    new Image(
                        maxSize: '5M',
                        mimeTypes: [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                    ),
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'required' => false,
                'attr' => [
                    'class' => 'js-ckeditor',
                    'rows' => 18,
                ],
            ])
            ->add('secondaryContent', TextareaType::class, [
                'label' => 'Contenu complémentaire',
                'required' => false,
                'help' => 'Texte affiché après les membres de l’équipe.',
                'attr' => [
                    'class' => 'js-ckeditor',
                    'rows' => 14,
                ],
            ])
            ->add('socialLinks', CollectionType::class, [
                'entry_type' => SocialLinkType::class,
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
                    'label' => 'Supprimer l’image actuelle',
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
            'data_class' => About::class,
            'show_remove_image' => false,
        ]);

        $resolver->setAllowedTypes(
            'show_remove_image',
            'bool',
        );
    }
}
