<?php

namespace App\Form;

use App\Entity\GameLink;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class GameLinkType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Steam, GOG, Nintendo eShop...',
                ],
            ])
            ->add('url', UrlType::class, [
                'label' => 'Lien',
                'attr' => [
                    'placeholder' => 'https://...',
                ],
            ])
            ->add('displayOrder', HiddenType::class);
    }

    public function configureOptions(
        OptionsResolver $resolver,
    ): void {
        $resolver->setDefaults([
            'data_class' => GameLink::class,
        ]);
    }
}
