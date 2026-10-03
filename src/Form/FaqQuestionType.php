<?php

namespace App\Form;

use App\Entity\FaqQuestion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FaqQuestionType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Question',
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Réponse',
                'attr' => ['rows' => 8],
            ])
            ->add('displayOrder', IntegerType::class, [
                'label' => 'Ordre',
                'attr' => ['min' => 0],
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'Visible publiquement',
                'required' => false,
            ]);
    }

    public function configureOptions(
        OptionsResolver $resolver,
    ): void {
        $resolver->setDefaults([
            'data_class' => FaqQuestion::class,
        ]);
    }
}
