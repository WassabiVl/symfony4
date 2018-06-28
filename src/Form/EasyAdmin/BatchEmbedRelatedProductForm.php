<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 30.10.2017
 * Time: 15:38
 */

namespace App\Form\EasyAdmin;

use App\Entity\Product;
use App\Entity\ProductCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BatchEmbedRelatedProductForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Specific product name',
            ])
            ->add('productCategory', EntityType::class,[
                'class' => ProductCategory::class,
                'Label'=>'Product Category'
            ])
            ->add('buyPrice', MoneyType::class, array('currency'=> 'EUR', 'label'=> 'Buy Price per 1MBQ'))
            ->add('halfLife',NumberType::class,array('required'=> true, 'label'=> 'Half-life'))
            ->add('slug')
            ;
    }

    /**
     * @param OptionsResolver $resolver
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver):void
    {
        $resolver->setDefaults(array(
           'data_class' => Product::class,
        ));
    }

}