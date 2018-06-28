<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 27/11/2017
 * Time: 12:04
 */

namespace App\Form\ProductOrder;

use App\Entity\OrderedProductCategory;
use App\Entity\OrderedProducts;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderedProductFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->add('amount',NumberType::class, array('label'=> 'amount') )
            ->add('orderedProductCategory',EntityType::class, array(
                'class' => OrderedProductCategory::class,
                'label'=>'Ordered Product Category'
            ))
            ->add('product',
                EntityType::class, array(
                    'class' => Product::class,
                    'label' => 'Product'
                ));
    }

    /**
     * @param OptionsResolver $resolver
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver):void
    {
        $resolver->setDefaults(array(
            'data_class' => OrderedProducts::class
        ));
    }
}