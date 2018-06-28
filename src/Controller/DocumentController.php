<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 13/11/2017
 * Time: 11:03
 */

namespace App\Controller;

use App\Entity\Carrier;
use App\Entity\Customer;
use App\Entity\Document;
use App\Entity\Order;
use App\Entity\OrderedProductCategory;
use Nzo\UrlEncryptorBundle\Annotations\ParamDecryptor;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\Translation\TranslatorInterface;

class DocumentController extends Controller
{
    private 
        $authorizationChecker, $translator;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker, TranslatorInterface $translator)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->translator = $translator;
    }

    /**
     * @Route("/download/{document}", name="downloadDocument")
     * @param int $document
     * @return Response
     * @internal param $id
     * @ParamDecryptor(params={"document"})
     * @throws InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws NotFoundHttpException
     * @throws \InvalidArgumentException
     * @throws AuthenticationCredentialsNotFoundException
     * @throws \LogicException
     */
    public function downloadAction(int $document): Response
    {
        $response = null;
        $translated = $this->translator->trans('System error Unauthorized Access');
        $downloadedFile = $this->getDoctrine()
            ->getRepository(Document::class)
            ->find($document);
        if ($downloadedFile !== null){
            // if the Admin and Super Admin are logged in, grant them direct access
            if ($this->authorizationChecker->isGranted('ROLE_ADMIN') || $this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')){
                $response = new Response('', 404);
                if ($downloadedFile) {
                    $response = new BinaryFileResponse($this->container->getParameter('doc_directory').DIRECTORY_SEPARATOR .$downloadedFile->getDocumentFile());
                }
                return $response;
            }
            //break the checking depending on the User and Document Type
            if ($downloadedFile->getDocType() === 'UG') {
                // UG is only for Customer
                if ($this->authorizationChecker->isGranted('ROLE_CARRIER') || $this->authorizationChecker->isGranted('ROLE_PRODUCER')) {
                    throw new NotFoundHttpException($translated);
                }

                if ($this->authorizationChecker->isGranted('ROLE_CUSTOMER')) {

                    /**
                     * @var $customer Customer
                     */
                    $customer = $this->getUser()->getRelatedCustomerEntry();
                    if ($customer->getRelatedUg() !== $downloadedFile) {
                        throw new NotFoundHttpException($translated);
                    }
                } else {
                    throw new NotFoundHttpException($translated);
                }
            }
            elseif ($downloadedFile->getDocType() === 'ADR') {
                if ($this->authorizationChecker->isGranted('ROLE_PRODUCER')){
                    throw new NotFoundHttpException($translated);
                }
                if ($this->authorizationChecker->isGranted('ROLE_CARRIER')){
                    /**
                     * @var Carrier $carrier
                     */
                    $carrier = $this->getUser()->getRelatedCarrierEntry();
                    /** @var OrderedProductCategory $OrderedProductCategory */
                    $OrderedProductCategory = $this->getDoctrine()->getRepository(OrderedProductCategory::class)->findOneBy(array('ADRDocument' => $downloadedFile));
                    if($OrderedProductCategory !== null){
                    $testCarrier = $OrderedProductCategory->getRelatedOrderedProduct()->getRelatedProduct()->getRelatedBatch()->getRelatedProducer()->getRelatedCarrier();
                        if($testCarrier !== $carrier) {
                            throw new NotFoundHttpException($translated);
                        }
                    }
                }elseif ($this->authorizationChecker->isGranted('ROLE_CUSTOMER')){
                    /**
                     * @var $customer Customer
                     */
                    $customer = $this->getUser()->getRelatedCustomerEntry();
                    /** @var OrderedProductCategory $OrderedProductCategory */
                    $OrderedProductCategory = $this->getDoctrine()->getRepository(OrderedProductCategory::class)->findOneBy(array('ADRDocument' => $downloadedFile));
                    if($OrderedProductCategory !== null){
                        $testCustomer = $OrderedProductCategory->getRelatedOrder()->getRelatedCustomer();
                        if($testCustomer !== $customer){
                            throw new NotFoundHttpException($translated);
                        }
                    }
                }
                else {
                    throw new NotFoundHttpException($translated);
                }
            } else { //the remaining to documents are OrderedConfirm and Bill that are only accessible by the Customer
                if ($this->authorizationChecker->isGranted('ROLE_CARRIER') || $this->authorizationChecker->isGranted('ROLE_PRODUCER')){
                    throw new NotFoundHttpException($translated);
                }

                if ($this->authorizationChecker->isGranted('ROLE_CUSTOMER')) {

                    /**
                     * @var $customer Customer
                     */
                    $customer = $this->getUser()->getRelatedCustomerEntry();
                        if($downloadedFile->getDocType() === 'OrderConfirmation'){
                            $testDocument = $this->getDoctrine()->getRepository(Order::class)->findOneBy(array('orderConformation'=> $downloadedFile));
                            if ($testDocument !== null){
                                $testCustomer = $testDocument->getRelatedCustomer();
                                if ($testCustomer !== $customer){
                                    throw new NotFoundHttpException($translated);
                                }
                            }
                        }
                    if ($downloadedFile->getDocType() === 'Bill') {
                        $testDocument = $this->getDoctrine()->getRepository(Order::class)->findOneBy(array('bill' => $downloadedFile));
                        if ($testDocument !== null){
                            $testCustomer = $testDocument->getRelatedCustomer();
                            if ($testCustomer !== $customer){
                                throw new NotFoundHttpException($translated);
                            }
                        }
                    }
                }else {
                    throw new NotFoundHttpException($translated);
                }
            }
            $response = new Response('', 404);
            if ($downloadedFile) {
                $response = new BinaryFileResponse($this->container->getParameter('doc_directory').DIRECTORY_SEPARATOR .$downloadedFile->getName());
            }
        }
        else {
            $response = null;
        }
        return $response;
    }

    /**
     * @param UploadedFile|File $file
     * @param Document|null $previousVersion
     * @param null $docType
     * @return Document
     * @throws \Exception
     */
    public function persistFile($file, Document $previousVersion = null, $docType =null): Document
    {
        $document = new Document();
        $document->setDocumentFile($file);
        $document->setPreviousVersion($previousVersion);
        $document->setDocType($docType);
        return $document;
    }
}