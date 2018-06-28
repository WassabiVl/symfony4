<?php
/**
 * Created by PhpStorm.
 * User: koske
 * Date: 07.11.2017
 * Time: 14:00
 */

namespace App\Form\FrontEnd;

use App\Entity\Customer;
use App\Form\DocumentType\UgDocumentTypeFormFOS;
use App\Form\NewAddressEmbedForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Exception\AccessException;

class CustomerEmbedFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            if (!array_key_exists('billAddress', $data) || '' === $data['billAddress']['companyName'] || '' === $data['billAddress']['firstName']) {
                $data['billAddress'] = $data['shippingAddress'];
                $event->setData($data);
            }
        });
        $builder
            ->add('isUgValid', HiddenType::class, array('data'=> 0,'label'=> 'Is UG Valid?'))
            ->add('relatedUg', UgDocumentTypeFormFOS::class, array('label' => false, 'required'=>true))
            ->add('shippingAddress', NewAddressEmbedForm::class,[
                'attr' => [
                    'class' => 'primary-address address-hide-double',
                    'data-same-address-text' => 'Haben Sie eine abweichende Rechnungsadresse?',
                ],
            ])
            ->add('billAddress', NewAddressEmbedForm::class,[
                'label' => 'Billing address',
                'required' => false,
                'attr' =>[
                    'class' => 'secondary-address address-hide-double'
                ]
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     * @throws \OutOfBoundsException
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver):void
    {
        $resolver->setDefaults(array(
            'data_class' => Customer::class,
            'validation_groups' => function(FormInterface $form) {
                $validation_groups = array('Registration');
                if($form->get('billAddress')->getData() === true) {
                    $validation_groups[] = 'billAddressRequired';
                }
                return $validation_groups;
            },
        ));
    }
}