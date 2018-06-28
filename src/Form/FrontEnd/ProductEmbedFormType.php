<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 12/01/2018
 * Time: 13:59
 */

namespace App\Form\FrontEnd;

use App\Entity\Batch;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Exception\AccessException;


class ProductEmbedFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->add('name')
            ->add('slug')
            ->add('buyPrice', MoneyType::class, array('currency'=> 'EUR', 'label'=> 'Buy Price per 1MBQ'))
            ->add('relatedBatch', EntityType::class, array('class' => Batch::class, 'label'=> 'Related Batch'))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver):void
    {
        $resolver->setDefaults(array(
            'data_class' => Product::class
        ));
    }
}