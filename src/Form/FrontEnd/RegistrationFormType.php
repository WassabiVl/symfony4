<?php
/**
 * Created by PhpStorm.
 * User: koske
 * Date: 06.11.2017
 * Time: 11:06
 */

namespace App\Form\FrontEnd;

use App\Entity\Account;
use App\Entity\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseRegistrationFormType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Exception\AccessException;

class RegistrationFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->add('gender', ChoiceType::class, array(
                'label'=>'gender',
                'choices' => array(
                    'Male' => 'Male',
                    'Female' => 'Female',
                    'Company' => 'Company'
                ),
            ))
            ->add('title',ChoiceType::class, array(
                'label'=>'Title',
                'choices'=>array(
                    'Dr.' => 'Dr.',
                    'Prof.' => 'Prof.',
                    'Prof. Dr.' => 'Prof. Dr.',
                    'Herr' => 'Herr',
                    'Frau' => 'Frau'
                )))
            ->add('firstName', TextType::class, array('label'=> ' First Name'))
            ->add('lastName', TextType::class, array('label'=> 'Last Name'))
            ->add('companyName', HiddenType::class, array('label'=> 'Company Name'))
            ->add('phone', TelType::class, array('required' => true, 'label'=> 'Phone Number'))
            ->add('fax',TelType::class, array('label'=> 'Fax Number'))
            ->add('relatedCustomerEntry', CustomerEmbedFormType::class, array(
                'data_class' => Customer::class,
                'label' => false
            ))
            ->add('type',HiddenType::class,array('data'=>'Customer'))
            //relatedCustomerEntry is used to get access to the relatedUg
            ->remove('username');
    }

    /**
     * @param OptionsResolver $resolver
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver):void
    {
        $resolver->setDefaults(array(
            'data_class' => Account::class
        ));
    }

    public function getParent(): ?string
    {
        return BaseRegistrationFormType::class;
    }

}