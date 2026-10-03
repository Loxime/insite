<?php

namespace App\Form;

use App\Entity\GameSection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

final class GameSectionType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de la section',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Titre optionnel',
                ],
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
            ->add('removeImage', CheckboxType::class, [
                'label' => 'Supprimer l’image actuelle',
                'mapped' => false,
                'required' => false,
                'row_attr' => [
                    'class' => 'admin-game-section__remove-image',
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Texte',
                'required' => false,
                'attr' => [
                    'class' => 'js-ckeditor',
                    'rows' => 12,
                ],
            ])
            ->add('displayOrder', HiddenType::class);
    }

    public function configureOptions(
        OptionsResolver $resolver,
    ): void {
        $resolver->setDefaults([
            'data_class' => GameSection::class,
        ]);
    }
}
