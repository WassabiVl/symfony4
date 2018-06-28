<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 30.10.2017
 * Time: 15:38
 */

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewAddressEmbedForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->add('companyName',TextType::class, array(
                'required'=> true,
                'label'=>'Company Name'
            ))
            ->add('title',ChoiceType::class, array('choices'=>array(
                'Dr.' => 'Dr.',
                'Prof.' => 'Prof.',
                'Prof. Dr.' => 'Prof. Dr.',
                'Herr' => 'Herr',
                'Frau' => 'Frau'
            ),
                'label'=>'Title'
            ))
            ->add('firstName', TextType::class, array(
                'required'=> true,
                'label'=>'First Name'
            ))
            ->add('lastName', TextType::class, array(
                'required'=> true,
                'label'=>'Last Name'
            ))
            ->add('street',TextType::class, array(
                'required'=> true,
                'label'=>'Street'
            ))
            ->add('buildingNumber', TextType::class, array(
                'required'=> true,
                'label' => 'Building/Street Number'
            ))
            ->add('zip', IntegerType::class, array(
                'required'=> true,
                'label'=>'ZIP'
            ))
            ->add('city',TextType::class, array(
                'required'=> true,
                'label'=>'City'
            ))
            ->add('state',TextType::class, array(
                'label'=> 'State/Province',
                'required'=> true
            ))
            ->add('country', CountryType::class)
            ->add('latitude', HiddenType::class, array('data'=> 0))
            ->add('longitude', HiddenType::class, array('data'=> 0))
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                array($this, 'onPreSetData')
            );
    }

    public function onPreSetData(FormEvent $event):void
    {

    }

    /**
     * @param OptionsResolver $resolver
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver):void
    {
        $resolver->setDefaults(array(
            'data_class' => Address::class,
            'label' => false,
            'validation_groups' => array('Address')
        ));
    }


}