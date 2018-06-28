<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 30.10.2017
 * Time: 15:38
 */

namespace App\Form\EasyAdmin;

use App\Entity\BatchLockTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BatchEmbedRelatedLockTimesForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->add('id',IntegerType::class,array('disabled'=>true))
            ->add('startTime', DateTimeType::class, ['date_format' => 'dd.MM.yyyy', 'label'=>'Start Time'])
            ->add('endTime', DateTimeType::class, ['date_format' => 'dd.MM.yyyy', 'label'=> 'End Time'])
            ;
    }

    /**
     * @param OptionsResolver $resolver
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver):void
    {
        $resolver->setDefaults(array(
           'data_class' => BatchLockTime::class,
        ));
    }

}