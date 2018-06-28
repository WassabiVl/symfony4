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
use App\Entity\Carrier;
use App\Entity\Producer;
use App\Form\AddressFormType;
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

class AccountEmbedProducerForm extends AbstractType
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
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $producer = $event->getData();
            $form = $event->getForm();
            if (!$producer || null === $producer->getId()) {
                $form->add('pickUpAddress',AddressFormType::class, array('label'=> 'Address'));
            } else {
                /**
                 * @var $data Account
                 */
                $account = $form->getParent();
                if ($account !== null){
                    $account = $account->getData();
                    $form->add('pickUpAddress', EntityType::class, [
                        'class' => Address::class,
                        'query_builder' => function (EntityRepository $er) use ($account) {
                            return $er->createQueryBuilder('u')
                                ->where('u.relatedAccount = ?1')
                                ->setParameter('1', $account->getId());
                        }
                    ]);
                }
            }});
        $builder
            ->add('number')
            ->add('relatedCarrier', EntityType::class,[
                'class' => Carrier::class,
                'query_builder' => function (EntityRepository $er) { //so that a deleted user is not selected
                    return $er->createQueryBuilder('u')
                        ->leftJoin('App:Account', 'a', 'WITH', 'u.id = a.id')
                        ->where('a.isDeleted = 0')
                        ;
                },

            ]);

    }

    /**
     * @param OptionsResolver $resolver
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver):void
    {
        $resolver->setDefaults(array(
            'data_class' => Producer::class,
        ));
    }
    public function onPreSubmit(FormEvent $event): void
    {

    }

}