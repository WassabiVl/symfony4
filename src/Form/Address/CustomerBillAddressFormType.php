<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 16/05/2018
 * Time: 15:40
 */

namespace App\Form\Address;

use App\Entity\Address;
use App\Entity\Customer;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerBillAddressFormType  extends AbstractType
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
            ->addEventListener(FormEvents::PRE_SET_DATA,function(FormEvent $event){
                /**
                 * @var $account Customer
                 */
                $account = $event->getData();
                $account= $account->getRelatedAccount();
                $event->getForm()
                    ->add('billAddress', EntityType::class,[
                        'label'=>'Billing Address',
                        'class' => Address::class,
                        'query_builder' => function(EntityRepository $er) use($account){
                            return $er->createQueryBuilder('u')
                                ->where('u.relatedAccount = ?1')
                                ->setParameter('1', $account->getId());
                        }
                    ]);
            })
        ;
    }

    /**
     * @param OptionsResolver $resolver
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver):void
    {
        $resolver->setDefaults(array(
            'data_class' => Customer::class
        ));
    }
}