<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\AboutProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class AboutProfileType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(max: 100),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(max: 100),
                ],
            ])
            ->add('slug', TextType::class, [
                'label' => 'Identifiant URL',
                'required' => false,
                'help' => 'Facultatif : généré depuis le prénom et le nom.',
                'constraints' => [
                    new Assert\Length(max: 220),
                ],
            ])
            ->add('role', TextType::class, [
                'label' => 'Rôle dans le studio',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(max: 160),
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Photo du membre',
                'mapped' => false,
                'required' => false,
                'help' => 'JPEG, PNG ou WebP — 5 Mo maximum.',
                'constraints' => [
                    new Assert\Image(
                        maxSize: '5M',
                        mimeTypes: [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                    ),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description courte',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Présentation complète',
                'required' => false,
                'attr' => [
                    'class' => 'js-ckeditor',
                    'rows' => 12,
                ],
            ])
            ->add('age', IntegerType::class, [
                'label' => 'Âge',
                'required' => false,
                'constraints' => [
                    new Assert\Range(min: 0, max: 130),
                ],
            ])
            ->add('languagesInput', TextareaType::class, [
                'label' => 'Langues',
                'mapped' => false,
                'required' => false,
                'data' => $options['languages_data'],
                'help' => 'Une langue par ligne ou séparée par une virgule.',
                'attr' => ['rows' => 3],
                'constraints' => [
                    new Assert\Length(max: 2000),
                ],
            ])
            ->add('skillsInput', TextareaType::class, [
                'label' => 'Compétences',
                'mapped' => false,
                'required' => false,
                'data' => $options['skills_data'],
                'help' => 'Une compétence par ligne ou séparée par une virgule.',
                'attr' => ['rows' => 4],
                'constraints' => [
                    new Assert\Length(max: 2000),
                ],
            ])
            ->add('displayOrder', IntegerType::class, [
                'label' => "Ordre d'affichage",
                'constraints' => [
                    new Assert\NotNull(),
                    new Assert\Range(min: 0, max: 10000),
                ],
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'Profil visible publiquement',
                'required' => false,
            ]);

        if ($options['show_remove_image']) {
            $builder->add('removeImage', CheckboxType::class, [
                'label' => 'Supprimer la photo actuelle',
                'mapped' => false,
                'required' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AboutProfile::class,
            'languages_data' => '',
            'skills_data' => '',
            'show_remove_image' => false,
        ]);

        $resolver->setAllowedTypes('languages_data', 'string');
        $resolver->setAllowedTypes('skills_data', 'string');
        $resolver->setAllowedTypes('show_remove_image', 'bool');
    }
}
