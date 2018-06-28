<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 22/01/2018
 * Time: 16:33
 */

namespace App\Form;

use App\Entity\Producer;
use App\Entity\Address;
use App\Form\FrontEnd\AccountEmbedFormType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProducerFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     *
     * @throws UnexpectedTypeException
     * @throws LogicException
     * @throws AlreadySubmittedException
     */
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->add('headTitle',ChoiceType::class, array('choices'=>array(
                'Dr.' => 'Dr.',
                'Prof.' => 'Prof.',
                'Prof. Dr.' => 'Prof. Dr.',
                'Herr' => 'Herr',
                'Frau' => 'Frau'
            )
            , 'label' => 'Head Title'))
            ->add('headFirstName', TextType::class, array('label'=> 'Head First Name')) // ToDo: Translation
            ->add('headLastName', TextType::class, array('label'=> 'Head Last Name'))
            ->add('relatedAccount', AccountEmbedFormType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA,function(FormEvent $event){
                /**
                 * @var $account Producer
                 */
                $account = $event->getData();
                $account= $account->getRelatedAccount();
                $event->getForm()
                    ->add('pickUpAddress', EntityType::class, [
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
            'data_class' => Producer::class,
            'label' => 'Your Information'
        ));
    }
}