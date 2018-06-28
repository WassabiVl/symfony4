<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 18/04/2018
 * Time: 15:54
 */

namespace App\Form\EasyAdmin;


use App\Entity\OrderedProductCategory;
use App\Entity\ProductCategory;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderedProductCategoryEmbedOrderForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->add('relatedProductCategory', EntityType::class, array(
                'class' => ProductCategory::class,
                'label' => 'Product'))
            ->add('relatedBulkDiscount', NumberType::class,array('label'=>' Bulk Discount'))
            ->add('deliveredAmount', NumberType::class,array('label'=>'Delivered Amount'))
            ->add('relatedBuyPrice', NumberType::class,array('label'=>'Buy Price per 1MBQ'))
            ->add('orderedAmount',ChoiceType::class, array('label' => 'Amount', 'required'=> true,
                'choices' =>array(
                    2000 => 2000,
                    4000 => 4000,
                    6000 => 6000,
                    8000 => 8000,
                    10000 =>10000,
                    12000 => 12000,
                    14000 => 14000,
                    16000 => 16000
                )
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver):void
    {
        $resolver->setDefaults(array(
            'data_class' => OrderedProductCategory::class
        ));
    }
}