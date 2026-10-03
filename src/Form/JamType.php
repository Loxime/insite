<?php

namespace App\Form;

use App\Entity\Jam;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class JamType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom interne',
            ])
            ->add('sourceName', TextType::class, [
                'label' => 'Source',
            ])
            ->add('sourceUrl', UrlType::class, [
                'label' => 'Lien de la source',
                'required' => false,
            ])
            ->add('mode', ChoiceType::class, [
                'label' => 'Mode',
                'choices' => [
                    'Aléatoire chaque jour' => Jam::MODE_RANDOM,
                    'Fixe par journée' => Jam::MODE_FIXED,
                ],
            ])
            ->add('permanent', CheckboxType::class, [
                'label' => 'Jam permanente',
                'required' => false,
            ])
            ->add('startDate', DateType::class, [
                'label' => 'Début',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('endDate', DateType::class, [
                'label' => 'Fin',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'Active',
                'required' => false,
            ])
            ->add('scheduleDays', CollectionType::class, [
                'entry_type' => JamScheduleDayType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'label' => false,
            ]);
    }

    public function configureOptions(
        OptionsResolver $resolver,
    ): void {
        $resolver->setDefaults([
            'data_class' => Jam::class,
        ]);
    }
}
