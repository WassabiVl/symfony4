<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 27/11/2017
 * Time: 11:41
 */

namespace App\Form\ProductOrder;

use App\Entity\Address;
use App\Entity\Customer;
use App\Entity\Order;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderFormType extends AbstractType
{
    public $user;
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $this->user = $options['user'];
        $builder
            ->add('targetTime', DateTimeType::class, [
                'date_widget' => 'single_text',
                'date_format' => 'dd.MM.yyyy',
                'label' => false,
                'time_widget' => 'choice',
                'with_minutes'=> true,
                'html5' => true,
                'hours'=> range(8,9),
                'minutes' => range(0,0),

            ])
            ->add('relatedCustomer', HiddenType::class, array('data_class' => Customer::class))
            ->add('comment',TextareaType::class, array('required' =>false, 'label'=> 'Comment'))
            ->add('flag', HiddenType::class, array('data' => 'flag'))
            ->add('isOptimized', HiddenType::class, array('data' => '0'))
            ->add('isFixed', HiddenType::class, array('data' => '0'))
            ->add('isRejected', HiddenType::class, array('data' => '0'))
            ->add('grantedDiscount', HiddenType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA,function(FormEvent $event)  {
                /**
                 * @var $account Customer
                 */
                $account= $this->user;
                $event->getForm()
                    ->add('customerShippingAddress', EntityType::class, [
                        'class' => Address::class,
                        'query_builder' => function(EntityRepository $er) use($account){
                            return $er->createQueryBuilder('u')
                                ->where('u.relatedAccount = ?1')
                                ->setParameter('1', $account->getId());
                        }
                    ])
                    ->add('customerBillingAddress', EntityType::class,[
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
            'data_class' => Order::class,
            'user' => null
        ));
    }
}