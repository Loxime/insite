<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\ChangelogEntry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class ChangelogEntryType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add('version', TextType::class, [
                'label' => 'Version',
                'attr' => ['placeholder' => '1.0.0'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(max: 50),
                    new Assert\Regex(
                        pattern: '/^[A-Za-z0-9][A-Za-z0-9._-]*$/',
                        message: 'Utilise uniquement des lettres, chiffres, points, tirets ou underscores.',
                    ),
                ],
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(max: 255),
                ],
            ])
            ->add('text', TextareaType::class, [
                'label' => 'Texte de la note',
                'attr' => ['rows' => 16],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChangelogEntry::class,
        ]);
    }
}
