<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 12/01/2018
 * Time: 16:10
 */

namespace App\Form\FrontEnd;

use App\Entity\OrderedProductCategory;
use App\Entity\ProductCategory;
use App\Form\DocumentType\ADRDocumentFormType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Exception\AccessException;

class OrderedProductCategoryEmbedForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->add('id')
            ->add('relatedProductCategory', EntityType::class, array(
                'class' => ProductCategory::class,
                'label' => 'Product Category'))
            ->add('relatedBulkDiscount', NumberType::class, array('label'=> 'Related Bulk Discount'))
            ->add('relatedBuyPrice', MoneyType::class, array('currency'=> 'EUR', 'label'=> 'Related Buy Price'))
            ->add('orderedAmount', NumberType::class, array('label'=> 'Ordered Amount'))
            ->add('deliveredAmount', NumberType::class, array('label'=> 'Delivered Amount'))
            ->add('ADRDocument',ADRDocumentFormType::class, array('label'=> 'ADR Document'))
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
            'label'=>false,
        ));
    }
}