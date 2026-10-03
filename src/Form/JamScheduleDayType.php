<?php

namespace App\Form;

use App\Entity\JamScheduleDay;
use App\Entity\JamTheme;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class JamScheduleDayType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add('date', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('theme', EntityType::class, [
                'class' => JamTheme::class,
                'choice_label' => 'name',
                'label' => 'Thème',
            ])
            ->add('color1', ColorType::class, [
                'label' => 'Couleur 1',
            ])
            ->add('color2', ColorType::class, [
                'label' => 'Couleur 2',
            ])
            ->add('color3', ColorType::class, [
                'label' => 'Couleur 3',
            ]);
    }

    public function configureOptions(
        OptionsResolver $resolver,
    ): void {
        $resolver->setDefaults([
            'data_class' => JamScheduleDay::class,
        ]);
    }
}
