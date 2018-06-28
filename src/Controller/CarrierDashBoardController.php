<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 25/01/2018
 * Time: 15:14
 */

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Batch;
use App\Entity\Carrier;
use App\Entity\CarrierCost;
use App\Entity\Document;
use App\Entity\OrderedProductCategory;
use App\Entity\OrderedProducts;
use App\Entity\Producer;
use App\Form\DocumentType\ADRDocumentFormType;
use DateTime;
use Ivory\GoogleMap\Control\ControlPosition;
use Ivory\GoogleMap\Control\ScaleControl;
use Ivory\GoogleMap\Control\ScaleControlStyle;
use Ivory\GoogleMap\MapTypeId;
use Ivory\GoogleMap\Overlay\Marker;
use Nzo\UrlEncryptorBundle\Annotations\ParamDecryptor;
use Psr\Log\LoggerInterface;
use Swift_Mailer;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Ivory\GoogleMap\Map;
use Ivory\GoogleMap\Base\Coordinate;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CarrierDashBoardController extends Controller
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(LoggerInterface $logger, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $this -> logger = $logger;
        $this->validator = $validator;
        $this->translator =$translator;
    }
    /**
     * @Route("/carrier/dashboard/{slug}", name="carrier_dashboard")
     * @Security("has_role('ROLE_CARRIER')")
     * @param Request $request
     * @param string|null $slug
     * @param Swift_Mailer $mailer
     * @return Response
     */
    public function showAction(Request $request, string $slug =null, Swift_Mailer $mailer): Response
    {

        //Variables to start with
        $em = $this->getDoctrine()->getManager();
        /** @var Carrier $carrier */
        $carrier = $this->getUser()->getRelatedCarrierEntry();
        $producer = $em->getRepository(Producer::class)->findBy(array('relatedCarrier' => $carrier));
        $relatedBatches = $em->getRepository(Batch::class)->findBy(array('relatedProducer' => $producer), array('id' => 'DESC'));
        $dateTimeNow = new DateTime('now');
        $orderNew =[]; //array for collection new orders
        $orderOld =[]; //array for collection old orders
        $orderMap =[]; //array for collection accepted orders
        $distance=[];
        $rejectedOrders = [];
        foreach ($relatedBatches as $relatedBatch){
            /** @var Batch $relatedBatch */
            /** @var OrderedProducts $relatedOrderedProduct */
            foreach ($relatedBatch->getRelatedProduct()->getRelatedOrderedProduct() as $relatedOrderedProduct){
                $targetTime = $relatedOrderedProduct->getOrderedProductCategory()->getRelatedOrder()->getTargetTime();
                if ($targetTime >= $dateTimeNow){
                    $orderNew[] = $relatedOrderedProduct;
                    if ($relatedOrderedProduct->getOrderedProductCategory()->getADRDocument()){
                        $orderMap[] = $relatedOrderedProduct;
                        $distance[$relatedOrderedProduct->getId()] = $em->getRepository(CarrierCost::class)
                            ->findOneBy(array(
                                'relatedProducer' => $relatedBatch->getRelatedProducer(),
                                'relatedCustomer' => $relatedOrderedProduct->getOrderedProductCategory()->getRelatedOrder()->getRelatedCustomer()))
                            ->getDistance();
                    } else {
                        $rejectedOrders[]=$relatedOrderedProduct;
                    }
                } elseif ($targetTime < $dateTimeNow){
                    $orderOld[] = $relatedOrderedProduct;
                }
            }
        }

        //Enable Pagination of the data retrieved using the KNP_Pagination Bundle
        $paginator = $this->get('knp_paginator');
        if ($slug === 'map'){
            if (!empty($rejectedOrders)){
                $this->sendRejectedEmail($rejectedOrders, $mailer);
            }
            $map = $this->renderCarrierMap($orderMap);
            $paginator = $paginator->paginate($orderMap, $request->query->getInt('page',1));
            return $this->render('user_dashboard/carrier_dashboard_map.html.twig', array( 'pagination' =>$paginator, 'distance' =>$distance, 'map'=>$map));


        }
        if($slug==='old') {
            $paginator = $paginator->paginate($orderOld, $request->query->getInt('page',1));
            return $this->render('user_dashboard/carrier_dashboard.html.twig', array( 'pagination' =>$paginator));
        }
        $paginator = $paginator->paginate($orderNew, $request->query->getInt('page',1));
        return $this->render('user_dashboard/carrier_dashboard.html.twig', array('pagination' => $paginator));
    }

    /**
     * @Route("/carrier/upload/{orderedProductCategory}", name= "carrier_upload")
     * @Security("has_role('ROLE_CARRIER')")
     * @param OrderedProductCategory $orderedProductCategory
     * @param Request $request
     * @return RedirectResponse|Response
     * @ParamDecryptor(params={"orderedProductCategory"})
     * @throws \LogicException
     */
    public function uploadAction(OrderedProductCategory $orderedProductCategory, Request $request){
        $em = $this->getDoctrine()->getManager();
        $previousDocVersion = $orderedProductCategory->getADRDocument();
        $document = new Document();
        $document->setPreviousVersion($previousDocVersion);
        $document->setDocType('ADR');
        $form = $this->createForm(ADRDocumentFormType::class, $document);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($document);
            $orderedProductCategory->setADRDocument($document);
            $em->persist($orderedProductCategory);
            $em->flush();
            return $this->redirectToRoute('carrier_dashboard');
        }
        return $this->render('user_dashboard/common_dashboard/upload.html.twig', array('form'=>$form->createView()));
    }

    /**
     * @param OrderedProducts $orders
     * @return Map
     */
    private function renderCarrierMap($orders): Map
    {
        $coordinates=null;
        $map = new Map();
        $scaleControl = new ScaleControl(
            ControlPosition::TOP_CENTER,
            ScaleControlStyle::DEFAULT_
        );
        /** @var Carrier $carrier */
        $carrier = $this->getUser()->getRelatedCarrierEntry();
        $map->setHtmlId('map_canvas');
        $map->setMapOption('mapTypeId', MapTypeId::HYBRID);
        $address = $carrier->getPrimaryAddress();
        if ($address) {
            $map->setCenter(new Coordinate($address->getLatitude(), $address->getLongitude()));
            $map->setMapOption('zoom', 6);
            $marker = new Marker(new Coordinate($address->getLatitude(), $address->getLongitude()));
            $marker->setOption('label', 'HQ');
            $map->getOverlayManager()->addMarker($marker);
            $map->getControlManager()->setScaleControl($scaleControl);
            if (null !== $orders) {
                /** @var OrderedProducts $order */
                foreach ($orders as $order) {
                    $customerAddress = $order->getOrderedProductCategory()->getRelatedOrder()->getRelatedCustomer()->getShippingAddress();
                    if($customerAddress){
                        $marker = new Marker(new Coordinate($customerAddress->getLatitude(), $customerAddress->getLongitude()));
                        $marker->setOption('label', $order->getOrderedProductCategory()->getRelatedOrder()->getId().'');
                        $map->getOverlayManager()->addMarker($marker);
                    }
                }
            }

            foreach ($carrier->getRelatedProducers() as $producer){
                $originAddress = $producer->getPickUpAddress();
                if ($originAddress){
                    $marker = new Marker(new Coordinate($originAddress->getLatitude(), $originAddress->getLongitude()));
                    $marker->setOption('label', 'P' . $producer->getId());
                    $map->getOverlayManager()->addMarker($marker);
                }else{
                    throw new \RuntimeException($this->translator->trans('Producer'). ' #' . $producer->getId().$this->translator->trans(' Address Was not found, Contact System Admin'));
                }
            }
        }else{
            throw new \RuntimeException($this->translator->trans('Your Address Was not found, Contact System Admin'));
        }
        return $map;
    }

    /**
     * @param $orders
     * @param Swift_Mailer $mailer
     */
    private function sendRejectedEmail($orders, Swift_Mailer $mailer): void
    {
        /** @var Account $account */
        $account = $this->getUser();

        $subject = $this->translator->trans('Carrier Rejected Orders');
        $admin =$account->getRelatedAdmin();
        if ($admin){
            //error checking
            $emailConstraint = new Email();
            $emailConstraint->message = 'Your customized error message';

            $errors = $this->validator->validate($admin->getRelatedAccount()->getEmail(),$emailConstraint);

            if(!$errors && $admin){

                // the message to the producer
                $messageProducer = (new \Swift_Message($subject))
                    ->setFrom('system')
                    ->setTo($admin->getRelatedAccount()->getEmail())
                    ->setBody(
                        $this->renderView('algorithm/algo_email_admin.html.twig'
                            ,array('name'=>$admin->getRelatedAccount()->getFirstName(), 'carrier' => $account->getRelatedCarrierEntry(), 'orders' => $orders))
                        , 'text/html'
                    )
                ;
                $mailer->send($messageProducer);
            }

            else{
                $this->logger->error($this->translator->trans('Admin ') .$admin . $this->translator->trans(' does not have a valid email'));
            }

        }else {
            $this->logger->error($this->translator->trans('Carrier ') .$account . $this->translator->trans(' does not have a valid admin'));

        }
    }
}