<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 30.10.2017
 * Time: 15:38
 */

namespace App\Form\EasyAdmin;

use App\Entity\Account;
use App\Entity\Address;
use App\Entity\Customer;
use App\Entity\DiscountGroup;
use App\Form\DocumentType\UgDocumentTypeForm;
use App\Form\NewAddressEmbedForm;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountEmbedCustomerForm extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws UnexpectedTypeException
     * @throws LogicException
     * @throws AlreadySubmittedException
     */
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();
                if (\is_array($data['billAddress']) && (!array_key_exists('billAddress', $data) || '' === $data['billAddress']['companyName'] || '' === $data['billAddress']['firstName'])) {
                    $data['billAddress'] = $data['shippingAddress'];
                    $event->setData($data);
                }
            });
        $builder
            ->add('institution', TextType::class, array('label'=> 'Institution'))
            ->add('customerNumber', TextType::class, array('label'=> 'Customer Number'))
            ->add('debitNumber', TextType::class, array('label'=> 'Debit Number'))
            ->add('toPayDate', DateTimeType::class, array('label'=> 'To Pay Date'))
            ->add('discountGroup', EntityType::class, array(
                'class' => DiscountGroup::class,
                'required' => false,
                'placeholder' => 'No Discount',
                'empty_data' => '',
                'label'=> 'Discount Group'
            ))
            ->add('relatedUg', UgDocumentTypeForm::class)
            ->add('isUgValid', ChoiceType::class, array(
                'label'=> 'Is Ug Valid?',
                'choices' => array(
                    'Yes' => true,
                    'No' => false
                )
            ));
        $action = 'new';
        if (isset($options['attr']['action_type'])) {
            $action = $options['attr']['action_type'];

        }
        if ($action === 'edit') {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();
                /**
                 * @var $data Account
                 */
                $account = $event->getForm()->getParent();
                if ($account !== null){
                    $account = $account->getData();
                    $form
                        ->add('shippingAddress', EntityType::class, [
                            'class' => Address::class,
                            'query_builder' => function (EntityRepository $er) use ($account) {
                                return $er->createQueryBuilder('u')
                                    ->where('u.relatedAccount = ?1')
                                    ->setParameter('1', $account->getId());
                            }
                        ])
                        ->add('billAddress', EntityType::class, [
                            'class' => Address::class,
                            'query_builder' => function (EntityRepository $er) use ($account) {
                                return $er->createQueryBuilder('u')
                                    ->where('u.relatedAccount = ?1')
                                    ->setParameter('1', $account->getId());
                            }
                        ]);
                }}
            );
        }
        else{
            $builder->add('shippingAddress', NewAddressEmbedForm::class, [
                'attr' => [
                    'class' => 'primary-address address-hide-double',
                    'data-same-address-text' => 'Do you have a different billing address?',
                ],
                'label' => 'Shipping address'
            ])
                ->add('billAddress', NewAddressEmbedForm::class, [
                    'label' => 'Bill address',
                    'attr' => [
                        'class' => 'secondary-address address-hide-double'
                    ]
                ]);
        }
    }

    /**
     * @param OptionsResolver $resolver
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => Customer::class,
        ));

    }
    public function onPreSubmit(FormEvent $event): void
    {

    }
}