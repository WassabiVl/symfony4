<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 15/01/2018
 * Time: 18:16
 */

namespace App\Form\FrontEnd;

use App\Entity\Account;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Exception\MissingOptionsException;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\OptionsResolver\Exception\AccessException;

class AccountEmbedFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws MissingOptionsException
     * @throws InvalidOptionsException
     * @throws ConstraintDefinitionException
     */
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->add('companyName',TextType::class, array(
                'required'=> true, 'label'=> 'Company Name'
            ))
            ->add('title',ChoiceType::class, array( 'label'=> 'Title','choices'=>array(
                'Dr.' => 'Dr.',
                'Prof.' => 'Prof.',
                'Prof. Dr.' => 'Prof. Dr.',
                'Herr' => 'Herr',
                'Frau' => 'Frau'
            )))
            ->add('firstName',TextType::class, array(
                'required'=> true, 'label'=> 'First Name'
            ))
            ->add('lastName',TextType::class, array(
                'required'=> true, 'label'=> 'Last Name'
            ))
            ->add('altMail', EmailType::class, array( 'label'=> 'Alternative Email'))
            ->add('phone', TelType::class, array('required' => true, 'label'=> 'Phone Number'))
            ->add('fax',TelType::class, array('required' => false, 'label'=> 'Fax Number'))
            ->add('gender', ChoiceType::class, array('label'=> 'Gender', 
                'choices' => array(
                    'Male' => '0',
                    'Female' => '1',
                    'Company' => '2'
                ),
            ))

        ;
    }

    /**
     * @param OptionsResolver $resolver
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver):void
    {
        $resolver->setDefaults(array(
            'data_class' => Account::class,
            'label' => false,
            'validation_groups' => array('Account')
        ));
    }
}