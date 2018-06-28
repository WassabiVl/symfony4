<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 12/01/2018
 * Time: 18:12
 */

namespace App\Form\FrontEnd;

use App\Entity\Batch;
use App\Entity\Product;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Exception\AccessException;

class BatchEmbedFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->add('id')
            ->add('dailyStartTime', DateTimeType::class, array('label'=>'Daily Start Time'))
            ->add('dailyEndTime', DateTimeType::class, array('label'=>'Daily End Time'))
            ->add('batchAmount', NumberType::class, array('label'=>'Batch Amount'))
            ->add('batchCost', MoneyType::class, array('currency'=> 'EUR', 'label'=> 'Batch Cost'))
            ->add('relatedProduct', EntityType::class, array('class'=> Product::class, 'label'=> 'Related Product'))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver):void
    {
        $resolver->setDefaults(array(
            'data_class' => Batch::class,
            'label'=> 'Batch'
        ));
    }
}