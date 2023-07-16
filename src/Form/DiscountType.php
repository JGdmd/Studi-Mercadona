<?php

namespace App\Form;

use App\Entity\Promotion;
use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class DiscountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'required' => true,
            ])
            ->add('discount', NumberType::class, [
                'required' => true,
            ])
            ->add('begins', DateTimeType::class, [
                'required' => true,
            ])
            ->add('ends', DateTimeType::class, [
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => $options['save_button_label']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Promotion::class,
            'save_button_label' => 'Créer',
        ]);
    }
}
