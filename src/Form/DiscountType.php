<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Promotion;
use App\Repository\ProductRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class DiscountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'placeholder' => 'Sélectionnez un produit',
                'required' => true,
                'choice_attr' => function($choice, $key, $value) {
                    if (null === $choice) {
                        return ['disabled' => 'disabled'];
                    }
                    return [];
                },
                'query_builder' => function (ProductRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.label', 'ASC');
                },
            ])
            ->add('discount', NumberType::class, [
                'required' => true,
            ])
            ->add('begins', DateType::class, [
                'required' => true,
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'YYYY-MM-dd',
            ])
            ->add('ends', DateType::class, [
                'required' => true,
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'YYYY-MM-dd',
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
