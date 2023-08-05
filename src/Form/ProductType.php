<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Unit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'required' => true,
            ])
            ->add('description', TextType::class, [
                'required' => true,
            ])
            ->add('price', NumberType::class, [
                'required' => true,
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image Produit',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => 'Format non accepté. Uniquement jpg / jpeg',
                    ])
                ],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'required' => true,
                'placeholder' => 'Sélectionnez une catégorie',
                'choice_attr' => function($choice, $key, $value) {
                    if (null === $choice) {
                        return ['disabled' => 'disabled'];
                    }
                    return [];
                },
            ])
            ->add('unit', EntityType::class, [
                'class' => Unit::class,
                'placeholder' => 'Sélectionnez une unité',
                'required' => true,
                'choice_attr' => function($choice, $key, $value) {
                    if (null === $choice) {
                        return ['disabled' => 'disabled'];
                    }
                    return [];
                },
            ])
            ->add('submit', SubmitType::class, [
                'label' => $options['save_button_label']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'save_button_label' => 'Créer',
        ]);
    }
}
