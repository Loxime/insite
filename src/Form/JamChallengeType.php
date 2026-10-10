<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\JamChallenge;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class JamChallengeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la JAM',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 160)],
            ])
            ->add('theme', TextType::class, [
                'label' => 'Thème de la JAM',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 255)],
            ])
            ->add('startsAt', DateTimeType::class, [
                'label' => 'Début (heure de Paris)',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'model_timezone' => 'UTC',
                'view_timezone' => 'Europe/Paris',
                'constraints' => [new Assert\NotNull()],
            ])
            ->add('endsAt', DateTimeType::class, [
                'label' => 'Fin (heure de Paris)',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'model_timezone' => 'UTC',
                'view_timezone' => 'Europe/Paris',
                'constraints' => [new Assert\NotNull()],
            ])
            ->add('colors', CollectionType::class, [
                'entry_type' => ColorType::class,
                'entry_options' => ['label' => false, 'constraints' => [new Assert\Regex('/^#[0-9a-fA-F]{6}$/')]],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'label' => false,
                'constraints' => [new Assert\Count(min: 1, max: 3)],
            ])
            ->add('platforms', CollectionType::class, [
                'entry_type' => JamPlatformType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'label' => false,
                'constraints' => [new Assert\Count(max: 12)],
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'Publier la JAM',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => JamChallenge::class]);
    }
}
