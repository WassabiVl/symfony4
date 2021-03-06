<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 21/03/2018
 * Time: 12:09
 */

namespace App\Form\DocumentType;

use App\Entity\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class UgDocumentTypeFormFOS extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->add('docType', HiddenType::class, array('data' => 'UG'))
            ->add('documentFile', VichFileType::class, [
                'required' => true,
                'allow_delete' => false,
                'label' => false,
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver):void
    {
        $resolver->setDefaults(array(
            'data_class' => Document::class
        ));
    }
}