<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 12/01/2018
 * Time: 13:55
 */

namespace App\Form\FrontEnd;

use App\Entity\ProductSellSizes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Exception\AccessException;

class ProductSellSizeEmbedForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->add('id',IntegerType::class, array('disabled' => true))
            ->add('amount', NumberType::class, array('label'=>'Amount'))
            ->add('discountInPercent', NumberType::class, array('label'=> 'Discount In Percentage'))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver):void
    {
        $resolver->setDefaults(array(
            'data_class' => ProductSellSizes::class
        ));
    }

    /**
     * @param FormEvent $event
     * @return FormEvent|FormInterface
     */
    public function onPostSetData(FormEvent $event)
    {
        if ($event->getData() && $event->getData()->getId()) {
            // unset($form['user']);
            return $event->getForm();
        }
        return $event;
    }
}