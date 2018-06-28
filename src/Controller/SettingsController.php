<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 15/06/2018
 * Time: 10:22
 */

namespace App\Controller;


use App\Entity\Carrier;
use App\Entity\Customer;
use App\Entity\Producer;
use App\Form\CarrierFormType;
use App\Form\CustomerFormType;
use App\Form\ProducerFormType;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class SettingsController extends Controller
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var TranslatorInterface
     */
    private $translator;


    public function __construct(AuthorizationCheckerInterface $authorizationChecker, LoggerInterface $logger, TranslatorInterface $translator)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this -> logger = $logger;
        $this->translator=$translator;
    }

    /**
     * @Route("/user/settings", name= "user_setting")
     * @param Request $request
     * @return RedirectResponse|Response
     * @Security("has_role('ROLE_CUSTOMER' or 'ROLE_PRODUCER' or 'ROLE_CARRIER')")
     * @throws \LogicException
     * @throws \Exception
     */
    public function settingsAction(Request $request)
    {
        $address = $this->getUser()->getRelatedAddresss();
        $previousVersion = null;
        $user = null;
        $form = null;
        $route = null;
        if ($this->authorizationChecker->isGranted('ROLE_PRODUCER')){
            /** @var Producer $user */
            $user = $this->getUser()->getRelatedProducerEntry();
            $form = $this->createForm(ProducerFormType::class, $user);
            $route = $this->redirectToRoute('producer_dashboard');
        }
        if ($this->authorizationChecker->isGranted('ROLE_CARRIER')){
            /** @var Carrier $user */
            $user = $this->getUser()->getRelatedCarrierEntry();
            $form = $this->createForm(CarrierFormType::class, $user);
            $route = $this->redirectToRoute('carrier_dashboard');
        }
        if ($this->authorizationChecker->isGranted('ROLE_CUSTOMER')){
            /** @var Customer $user */
            $user = $this->getUser()->getRelatedCustomerEntry();
            $form = $this->createForm(CustomerFormType::class, $user);
            $route = $this->redirectToRoute('customer_dashboard');
            $previousVersion = $user->getRelatedUg();
        }
        if ($user === null) {
            $this->logger->error($this->translator->trans('Access denied trying to use settings controller of user: ').$this->getUser().$this->translator->trans(' problem to the settings controller'));
            return $this->redirectToRoute('homepage');
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->authorizationChecker->isGranted('ROLE_CUSTOMER')){
                $data = $form->getNormData()->getRelatedUg()->getDocumentFile();
                if ($data) {
                    $document = $this->get(DocumentController::class)->persistFile($data, $previousVersion, 'UG');
                    $user->setRelatedUg($document);
                }
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return $route;
        }

        return $this->render('user_dashboard/common_dashboard/setting.html.twig', array('form'=>$form->createView(), 'addresses' => $address));
    }
}