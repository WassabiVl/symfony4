<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 16/01/2018
 * Time: 18:35
 */

namespace App\Form\FrontEnd;

use App\Entity\BatchLockTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Exception\AccessException;

class BatchLockTimeFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->add('id' )
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
            'label'=> 'Batch Lock Time'
        ));
    }
}