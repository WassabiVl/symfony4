<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 27/11/2017
 * Time: 12:15
 */

namespace App\Form\ProductOrder;

use App\Entity\Order;
use App\Entity\OrderedProductCategory;
use App\Entity\ProductCategory;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderedProductCategoryFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->add('relatedOrder',OrderFormType::class, array(
                'data_class' => Order::class,
                'label' => false,
                'user'=> $options['user']
            ))
            ->add('relatedProductCategory', EntityType::class, array(
                'class' => ProductCategory::class,
                'label' => 'Product'))
            ->add('relatedBulkDiscount', HiddenType::class, array('data' => '0'))
            ->add('deliveredAmount', HiddenType::class, array('data' => '0'))
            ->add('relatedBuyPrice', HiddenType::class)
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
            ->add('submit', SubmitType::class, array('label'=> 'Submit'))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver):void
    {
        $resolver->setDefaults(array(
            'data_class' => OrderedProductCategory::class,
            'user' =>null
        ));
    }
}