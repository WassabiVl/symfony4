<?php
/**
 * Created by PhpStorm.
 * User: koske
 * Date: 20.11.2017
 * Time: 16:34
 */

namespace App\Form\FrontEnd;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Exception\AccessException;

class AddressEmbedForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->add('type')
            ->add('companyName',TextType::class, array(
                'required'=> true, 'label'=> 'Company Name'
            ))
            ->add('title',ChoiceType::class, array( 'label'=> 'Tile','choices'=>array(
                'Dr.' => 'Dr.',
                'Prof.' => 'Prof.',
                'Prof. Dr.' => 'Prof. Dr.',
                'Herr' => 'Herr',
                'Frau' => 'Frau'
            )))
            ->add('firstName', TextType::class, array(
                'required'=> true, 'label'=> 'First Name'
            ))
            ->add('lastName', TextType::class, array(
                'required'=> true, 'label'=> 'Last Name'
            ))
            ->add('street',TextType::class, array(
                'required'=> true, 'label'=> 'Street'
            ))
            ->add('buildingNumber', TextType::class, array(
                'required'=> true,
                'label' => 'Building/Street Number'
            ))
            ->add('zip', IntegerType::class, array(
                'required'=> true , 'label'=> 'Zip Code'
            ))
            ->add('city',TextType::class, array(
                'required'=> true, 'label'=> 'City'
            ))
            ->add('state',TextType::class, array(
                'label'=> 'State/Province',
                'required'=> true
            ))
            ->add('country', CountryType::class, array('label'=> 'Country'))

        ;
    }

    /**
     * @param OptionsResolver $resolver
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver):void
    {
        $resolver->setDefaults(array(
            'data_class' => Address::class,
            'label' => false
        ));
    }
}