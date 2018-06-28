<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 12/01/2018
 * Time: 17:54
 */

namespace App\Form\FrontEnd;

use App\Entity\Carrier;
use App\Entity\CarrierCost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Exception\AccessException;

class CarrierEmbedFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->add('id')
            ->add('costPerKm', MoneyType::class, array('currency'=> 'EUR', 'label' => 'Cost per Kilometer'))
            ->add('relatedCarrierCost', CollectionType::class, array('data_class'=> CarrierCost::class, 'label'=> 'Related Carrier Cost'))

        ;
    }

    /**
     * @param OptionsResolver $resolver
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver):void
    {
        $resolver->setDefaults(array(
            'data_class' => Carrier::class,
            'label' => false
        ));
    }
}