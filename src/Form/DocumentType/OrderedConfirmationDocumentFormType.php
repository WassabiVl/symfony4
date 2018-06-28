<?php
/**
 * Created by IntelliJ IDEA.
 * User: Wassabi.vl
 * Date: 09-Jan-18
 * Time: 01:45
 */

namespace App\Form\DocumentType;

use App\Entity\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class OrderedConfirmationDocumentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->add('docType', HiddenType::class, array('data' => 'OrderConfirmation'))
            ->add('documentFile', VichFileType::class,[
                'required' => false,
                'allow_delete' => false,
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