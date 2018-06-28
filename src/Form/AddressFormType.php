<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 22/03/2018
 * Time: 10:35
 */

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->add('title', ChoiceType::class, array('choices' => array(
                'Dr.' => 'Dr.',
                'Prof.' => 'Prof.',
                'Prof. Dr.' => 'Prof. Dr.',
                'Herr' => 'Herr',
                'Frau' => 'Frau'
            ), 'label'=> 'Title'
            ))
            ->add('firstName', TextType::class, array('label'=> ' First Name'))
            ->add('lastName', TextType::class, array('label'=> 'Last Name'))
            ->add('street', TextType::class, array('label'=> ' Street'))
            ->add('buildingNumber', TextType::class, array('label'=> 'Building/Street Number'))
            ->add('zip', NumberType::class, array('label'=> 'Zip Code'))
            ->add('city', TextType::class, array('label'=> 'City'))
            ->add('state', TextType::class, array('label'=> 'State/Province'))
            ->add('country', CountryType::class, array('data'=> 'de'))
            ->add('companyName', TextType::class, array('label'=> 'Company Name'))
            ->add('latitude', HiddenType::class, array('data'=> 0))
            ->add('longitude', HiddenType::class, array('data'=> 0))

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