<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\PostCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;

final class PostType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('tags', EntityType::class, [
                'class' => PostCategory::class,
                'choice_label' => 'libelle',
                'label' => 'Tags',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'by_reference' => false,
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
                        maxSizeMessage: 'L’image ne doit pas dépasser 5 Mo.',
                        mimeTypesMessage: 'Utilise une image JPEG, PNG ou WebP.',
                    ),
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'required' => false,
                'constraints' => [
                    new NotBlank(
                        message: 'Le contenu de l’article est obligatoire.',
                    ),
                ],
                'attr' => [
                    'class' => 'js-ckeditor',
                    'rows' => 18,
                ],
            ])
            ->add('metaTitle', TextType::class, [
                'label' => 'Meta title',
                'required' => false,
                'attr' => [
                    'maxlength' => 255,
                    'placeholder' => 'Titre SEO optionnel',
                ],
            ])
            ->add('metaDescription', TextareaType::class, [
                'label' => 'Meta description',
                'required' => false,
                'attr' => [
                    'maxlength' => 320,
                    'rows' => 4,
                    'placeholder' => 'Résumé court de l’article',
                ],
            ])
            ->add('primaryKeyword', TextType::class, [
                'label' => 'Keyword principal',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Mot-clé principal',
                ],
            ])
            ->add('secondaryKeywords', TextareaType::class, [
                'label' => 'Keywords secondaires',
                'required' => false,
                'help' => 'Un mot-clé par ligne.',
                'attr' => [
                    'rows' => 5,
                    'placeholder' => "keyword 1\nkeyword 2\nkeyword 3",
                ],
            ]);

        if ($options['show_remove_image']) {
            $builder->add('removeImage', CheckboxType::class, [
                'label' => 'Supprimer l’image actuelle',
                'mapped' => false,
                'required' => false,
            ]);
        }

        $builder
            ->get('secondaryKeywords')
            ->addModelTransformer(
                new CallbackTransformer(
                    static function (?array $keywords): string {
                        return implode(PHP_EOL, $keywords ?? []);
                    },
                    static function (?string $keywords): ?array {
                        if ($keywords === null || trim($keywords) === '') {
                            return null;
                        }

                        $values = preg_split(
                            '/[\r\n,]+/',
                            $keywords,
                        );

                        if ($values === false) {
                            return null;
                        }

                        $values = array_map(
                            static fn (string $value): string => trim($value),
                            $values,
                        );

                        $values = array_filter(
                            $values,
                            static fn (string $value): bool => $value !== '',
                        );

                        $values = array_values(
                            array_unique($values),
                        );

                        return $values === [] ? null : $values;
                    },
                ),
            );
    }

    public function configureOptions(
        OptionsResolver $resolver,
    ): void {
        $resolver->setDefaults([
            'data_class' => Post::class,
            'show_remove_image' => false,
        ]);

        $resolver->setAllowedTypes(
            'show_remove_image',
            'bool',
        );
    }
}
