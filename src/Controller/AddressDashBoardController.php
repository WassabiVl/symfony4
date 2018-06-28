<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 24/01/2018
 * Time: 13:25
 */

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Address;
use App\Form\Address\CarrierAddressFrom;
use App\Form\Address\CustomerBillAddressFormType;
use App\Form\Address\CustomerShipAddressFormType;
use App\Form\Address\ProducerAddressFormType;
use App\Form\NewAddressEmbedForm;
use http\Exception\RuntimeException;
use Ivory\GoogleMap\Service\Geocoder\Request\GeocoderAddressRequest;
use Nzo\UrlEncryptorBundle\Annotations\ParamDecryptor;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\TranslatorInterface;

class AddressDashBoardController extends Controller

{

    private
        $authorizationChecker;

    /**
     * Log constructor
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * AddressDashBoardController constructor.
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param LoggerInterface $logger
     * @param TranslatorInterface $translator
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, LoggerInterface $logger, TranslatorInterface $translator)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this -> logger = $logger;
        $this->translator = $translator;
    }

    /**
     * @Route("/setting/address/new/{slug}", name= "address_new")
     * @param Request $request
     * @param $slug
     * @return RedirectResponse|Response
     * @Security("has_role('ROLE_CUSTOMER' or 'ROLE_PRODUCER' or 'ROLE_CARRIER')")
     * @throws \LogicException
     */
    public function newAddressAction(Request $request, $slug = null){
        try{
            $em = $this->getDoctrine()->getManager();
        } catch(\LogicException $e){
            $this -> logger -> emergency('Doctrine in Address Controller failed: '.$e -> getMessage());
            $this->addFlash('error','Doctrine Failed');
            $em = null;
        }
        try{
            /**
             * @var $account Account
             */
            $account = $this->getUser();
        } catch (\LogicException $e){
            $this -> logger -> emergency('FOS getUser in Address Controller failed: '.$e -> getMessage());
            $this->addFlash('error','Find User Failed');
            $account = null;
        }
        $address = new Address();
        $address->setRelatedAccount($account);
        $form = $this->createForm(NewAddressEmbedForm::class, $address);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $route = null;
            //add lat and lon
            $coordinates = null;
            $requestAddress =
                $form->get('street')->getData() . ' ' .
                $form->get('buildingNumber')->getData(). ', ' .
                $form->get('zip')->getData(). ' ' .
                $form->get('city')->getData(). ', ' .
                $form->get('country')->getData();
            $request = new GeocoderAddressRequest($requestAddress);
            try
            {
                $response = $this->container->get('ivory.google_map.geocoder')->geocode($request);
                $results = $response->getResults();
                $coordinates = $results[0]->getGeometry()->getLocation();
                $address->setLatitude((float)$coordinates->getLatitude());
                $address->setLongitude((float)$coordinates->getLongitude());
                $this->addFlash('success','address added');
            } catch (\Exception $e){

                $this->addFlash('error',$this-$this->translator->trans('could not locate address'));
                $this->logger->error('could not locate address:' .$e . 'in newAddressController ' );
                $address->setLatitude(0);
                $address->setLongitude(0);
            }

            $em->persist($address);
            $translated = $this->translator->trans('Address Successfully saved!');
            $this->addFlash(
                'success',
                $translated
            );
            if ($this->authorizationChecker->isGranted('ROLE_CUSTOMER')) {
                $customer = $account->getRelatedCustomerEntry();
                if ($slug === 'ship' && $customer){
                    $customer->setShippingAddress($address);
                    $em->persist($customer);
                }
                elseif ($slug === 'bill' && $customer){
                    $customer->setBillAddress($address);
                    $em->persist($customer);
                } else {
                    $em->persist($address);
                }

                $route ='customer_dashboard';
            }

            if ($this->authorizationChecker->isGranted('ROLE_PRODUCER')) {
                $producer = $account->getRelatedProducerEntry();
                if ($producer){
                $producer->setPickUpAddress($address);
                $em->persist($producer);
                $route ='producer_dashboard';
                }
            }

            if ($this->authorizationChecker->isGranted('ROLE_CARRIER')) {
                $carrier = $account->getRelatedCarrierEntry();
                if ($carrier){
                $carrier->setPrimaryAddress($address);
                }
                $em->persist($carrier);
                $route = 'carrier_dashboard';
            }

            $em->flush();
            return $this->redirectToRoute($route);
        }
        return $this->render('user_dashboard/common_dashboard/address.html.twig', array('form'=>$form->createView(), 'type'=>$slug));
    }

    /**
     * @Route("/setting/address/edit/{address}", name= "address_edit")
     * @param Request $request
     * @param Address $address
     * @return RedirectResponse|Response
     * @Security("has_role('ROLE_CUSTOMER' or 'ROLE_PRODUCER' or 'ROLE_CARRIER')")
     * @ParamDecryptor(params={"address"})
     * @throws NotFoundHttpException
     * @throws \LogicException
     */
    public function editAddressAction(Request $request, Address $address){
        try{
            $this->checkAddress($address);}
        catch (\UnexpectedValueException $e){
            $this -> logger -> error('UnexpectedValueException in Address Controller check checkAddress  '.$e -> getMessage());
        }
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(NewAddressEmbedForm::class, $address);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //add lat and lon of the address if it can be found
            $coordinates = null;
            $toleranceValue = 0;
            $requestAddress =
                $form->get('street')->getData() . ' ' .
                $form->get('buildingNumber')->getData(). ', ' .
                $form->get('zip')->getData(). ' ' .
                $form->get('city')->getData(). ', ' .
                $form->get('country')->getData();
            /** @var GeocoderAddressRequest $request */
            $request = new GeocoderAddressRequest($requestAddress);
            while ($coordinates === null and $toleranceValue < 5)
            {
                $toleranceValue++;
                $response = $this->container->get('ivory.google_map.geocoder')->geocode($request);
                $results = $response->getResults();
                if ($results === null || empty($results)){
                    $coordinates = null;
                } else {
                    $coordinates = $results[0]->getGeometry()->getLocation();
                }

            }
            if (!$coordinates){
                $address->setLatitude(0);
                $address->setLongitude(0);
            } else {
                $address->setLatitude((float)$coordinates->getLatitude());
                $address->setLongitude((float)$coordinates->getLongitude());
            }
            $em->persist($address);
            $em->flush();
            $translated = $this->translator->trans('Address Successfully saved!');
            $this->addFlash(
                'success',
                $translated
            );
            return $this->redirectToRoute('user_setting');

        }
        return $this->render('user_dashboard/common_dashboard/address.html.twig', array('form'=>$form->createView()));
    }

    /**
     * @Route("/setting/address/delete/{address}", name= "address_remove")
     * @param Address $address
     * @return RedirectResponse|Response
     * @Security("has_role('ROLE_CUSTOMER' or 'ROLE_PRODUCER' or 'ROLE_CARRIER')")
     * @ParamDecryptor(params={"address"})
     * @throws \UnexpectedValueException
     * @throws NotFoundHttpException
     * @throws \LogicException
     */
    public function deleteAddressAction(Address $address){
        $this->checkAddress($address);
        $em = $this->getDoctrine()->getManager();

        if ($this->authorizationChecker->isGranted('ROLE_CUSTOMER')) {
            $shippingAddress = $this->getUser()->getRelatedCustomerEntry()->getShippingAddress();
            $billAddress = $this->getUser()->getRelatedCustomerEntry()->getBillAddress();
            if ($address === $shippingAddress || $address === $billAddress){
                $translated = $this->translator->trans('The Address You are trying to delete is either your Shipping Address or Billing Address');
                $this->addFlash(
                    'danger',$translated

                );
                return $this->redirectToRoute('user_setting');
            }
            $translated = $this->translator->trans('Your address has been deleted Successfully!');

            $em -> remove($address);
            $em -> flush();
            $this->addFlash(
                'success',
                $translated
            );
        }

        if ($this->authorizationChecker->isGranted('ROLE_PRODUCER')) {
            $pickupAddress= $this->getUser()-> getRelatedProducerEntry()->getPickUpAddress();
            if ($address === $pickupAddress){
                $translated = $this->translator->trans('The Address You are trying to delete is Your PickUp Address');
                $this->addFlash(
                    'danger',
                    $translated
                );
                return $this->redirectToRoute('user_setting');
            }
            $em -> remove($address);
            $em -> flush();
            $translated = $this->translator->trans('Your address has been deleted Successfully!');
            $this->addFlash(
                'success',
                $translated
            );
        }

        if ($this->authorizationChecker->isGranted('ROLE_CARRIER')) {
            $primaryAddress = $this->getUser()->getRelatedCarrierEntry()->getPrimaryAddress();
            if ($address === $primaryAddress) {
                $translated = $this->translator->trans('The Address You are trying to delete is Your Primary Address');
                $this->addFlash(
                    'danger', $translated
                );
                return $this->redirectToRoute('user_setting');
            }
            $em -> remove($address);
            $em -> flush();
            $translated = $this->translator->trans('Your address has been deleted Successfully!');
            $this->addFlash(
                'success',
                $translated
            );
        }
        return $this->redirectToRoute('user_setting');
    }

    /**
     * @param Address $address
     * @return bool
     * protection against URl manipulation or failure
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws NotFoundHttpException
     */
    public function checkAddress(Address $address): bool
    {
        $userCheck = $this->getDoctrine()->getRepository(Address::class)->findBy(array('relatedAccount' => $this->getUser()));
        if (!\in_array($address, $userCheck, true)){
            $translated = $this->translator->trans('System error Unauthorized Access');
            throw new NotFoundHttpException($translated);
        }
        return true;

    }

    /**
     * @Route("/setting/address/change/{slug}", name= "address_change")
     * @param Request $request
     * @param null $slug
     * @return Response|null
     * @Security("has_role('ROLE_CUSTOMER' or 'ROLE_PRODUCER' or 'ROLE_CARRIER')")
     */
    public function changeAddress(Request $request, $slug = null): ?Response
    {
        /** @var Account $user */
        $user = $this->getUser();
        if ($this->authorizationChecker->isGranted('ROLE_CUSTOMER')) {
            $customerAddressForm = null;
            $customer = $user->getRelatedCustomerEntry();
            if ($slug === 'ship') {
                $customerAddressForm = $this->createForm(CustomerShipAddressFormType::class, $customer);
            }
            elseif ($slug === 'bill') {
                $customerAddressForm = $this->createForm(CustomerBillAddressFormType::class, $customer);
            }
            elseif ($slug === null){
                throw new RuntimeException('slug not defined if bill or ship');
            }
            $customerAddressForm->handleRequest($request);
            if ($customerAddressForm->isSubmitted() && $customerAddressForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($customer);
                $em->flush();
                return $this->redirectToRoute('customer_dashboard');
            }
            return $this->render('user_dashboard/common_dashboard/edit_address.html.twig', array('form'=>$customerAddressForm->createView(),'type'=>$slug));
        }
        if ($this->authorizationChecker->isGranted('ROLE_PRODUCER')) {
            $slug = 'producer';
            $producer = $user->getRelatedProducerEntry();
            $producerAddressForm = $this->createForm(ProducerAddressFormType::class,$producer);
            $producerAddressForm->handleRequest($request);
            if ($producerAddressForm->isSubmitted() && $producerAddressForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($producer);
                $em->flush();
                return $this->redirectToRoute('producer_dashboard');
            }
            return $this->render('user_dashboard/common_dashboard/edit_address.html.twig', array('form'=>$producerAddressForm->createView(),'type'=>$slug));
        }
        if ($this->authorizationChecker->isGranted('ROLE_CARRIER')) {
            $slug = 'carrier';
            $carrier = $user->getRelatedCarrierEntry();
            $carrierAddressForm = $this->createForm(CarrierAddressFrom::class,$carrier);
            $carrierAddressForm->handleRequest($request);
            if ($carrierAddressForm->isSubmitted() && $carrierAddressForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($carrier);
                $em->flush();
                return $this->redirectToRoute('carrier_dashboard');
            }
            return $this->render('user_dashboard/common_dashboard/edit_address.html.twig', array('form'=>$carrierAddressForm->createView(),'type'=>$slug));
        }
        return null;
    }

    /**
     * @Route("/setting/address/show", name= "address_show")
     * @return Response
     * @Security("has_role('ROLE_CUSTOMER' or 'ROLE_PRODUCER' or 'ROLE_CARRIER')")
     */
    public function showAddress(): Response
    {
        /** @var Account $user */
        $user = $this->getUser();
        $address = $user->getRelatedAddresss();
        return $this->render('user_dashboard/common_dashboard/show_address.html.twig', array('addresses'=>$address));
    }
}