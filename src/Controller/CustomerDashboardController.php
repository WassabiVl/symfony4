<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 27/11/2017
 * Time: 14:16
 */

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderedProductCategory;
use App\Entity\ProductCategory;
use App\Entity\ProductSellSizes;
use App\Form\ProductOrder\OrderedProductCategoryFormType;
use DateTime;
use Nzo\UrlEncryptorBundle\Annotations\ParamDecryptor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CustomerDashboardController extends Controller
{
    /**
     * @Route("/order/product/new/{productCategory}/{amount}/{comment}", name="order_product")
     * @param Request $request
     * @param SessionInterface $session
     * @param ProductCategory $productCategory
     * @param $amount
     * @param null $comment
     * @return Response
     * @Security("has_role('ROLE_CUSTOMER')")
     */
    public function orderProductAction(Request $request, SessionInterface $session, ProductCategory $productCategory = null ,$amount = null, $comment = null ): Response
    {
        //cause nulls ar string in html
        $amount = (int)$amount;
        //Call required variables
        $em = $this->getDoctrine()->getManager();
        $orderProductCategory = new OrderedProductCategory();
        $order = new Order();

        //Add the User automatically from who is logged in
        /**
         * @var $customer Customer
         */
        $customer = $this->getUser()->getRelatedCustomerEntry();
        $order->setRelatedCustomer($customer);
        $order->setIsFixed(false);
        $order->setIsRejected(false);
        $order->setIsOptimized(false);

        //Set the DateTime to now to make it easier for the user to select future date
        $order->setTargetTime(new DateTime('tomorrow'));
        $order->setDateOrdered(new DateTime('now'));
        $order->setComment($comment);

        //to save the preConfiguration
        $em->persist($order);

        //set relevant data to the OrderedProductCategory
        $orderProductCategory->setRelatedOrder($order);
        $orderProductCategory->setRelatedProductCategory($productCategory);
        $orderProductCategory->setOrderedAmount($amount);
        $em->persist($orderProductCategory);
        //create the Form to upload data
        $baseForm = $this->createForm(OrderedProductCategoryFormType::class, $orderProductCategory, array('user'=>$this->getUser()));
        $baseForm->handleRequest($request);

        if ($baseForm->isSubmitted() && $baseForm->isValid())
        {
            //get related Price
            $price = $baseForm->get('relatedProductCategory')->getData();
            $priceBuy = $price->getSellPrice();
            $targetTime = $baseForm->getData()->getRelatedOrder()->getTargetTime();
            if($this->checkIfSameOrderDate($targetTime)){
                $this->addFlash('error',$this->get('translator')->trans('You have an order for the same day, please change the date'));
                return $this->render('user_dashboard/order_product.html.twig', array('form'=> $baseForm->createView()));

            }

            //get related discount
            $discountGroup = $customer->getDiscountGroup();
            if ($discountGroup && $discountGroup->getDateTimeEnd() > new DateTime('now') && $discountGroup->getDateTimeStart() < new DateTime('now')){
                $discountPrice = (float)$discountGroup->getDiscountInPercent();
            } else{
                $discountPrice=0;
            }

            //Set related price / discount
            $orderProductCategory->setRelatedBuyPrice($priceBuy);
            $orderProductCategory->setRelatedBulkDiscount($discountPrice);
            $em->persist($orderProductCategory);

            //add the data to the object
            $session->set('em',$orderProductCategory);
            return $this->redirectToRoute('order_product_confirm');
        }
        return $this->render('user_dashboard/order_product.html.twig', array('form'=> $baseForm->createView()));
    }

    /**
     * @Route("/order/product/confirm", name="order_product_confirm")
     * @param SessionInterface $session
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * @return Response
     * @Security("has_role('ROLE_CUSTOMER')")
     * @throws \LogicException
     * @throws \OutOfBoundsException
     */
    public function confirmAction(SessionInterface $session, Request $request, \Swift_Mailer $mailer): Response
    {

        /**
         * @var $data OrderedProductCategory
         * @var $user Account
         */
        $data = $session->get('em');
        //handle error if customer get to this page by mistake or hits back on after confirming.

        if (!$data){
            return $this->redirectToRoute('order_product');
        }
        $user = $this->getUser();
        $data->getRelatedOrder()->setRelatedCustomer($user->getRelatedCustomerEntry());
        $productCategory= $data->getRelatedProductCategory();
        $productSellPrice = $data->getOrderedAmount();
        $productTierDiscount = $this->getDoctrine()
            ->getRepository(ProductSellSizes::class)
            ->findOneBy(array('amount'=>$productSellPrice, 'relatedProductCategory'=> $productCategory));
        if ($productTierDiscount){
            $productTierDiscount = $productTierDiscount->getDiscountInPercent();
        } else{
            $productTierDiscount = 0;
        }
        //create a pseudo form to alter between confirming the order or changing it
        $builder= $this->createFormBuilder()
            ->add('Change', SubmitType::class)
            ->add('Submit', SubmitType::class)
            ->getForm();
        $builder->handleRequest($request);

        /**
         * @var $change SubmitButton
         * @var $submit SubmitButton
         */
        $change = $builder->get('Change');
        $submit = $builder->get('Submit');
        //confirm order clicked
        if ($builder->isSubmitted() && $builder->isValid()){
            if ($submit->isClicked()) {
                $em = $this->getDoctrine()->getManager();

                // to solve the problem of lost data transiting between pages
                $data->getRelatedOrder()->setRelatedCustomer($user->getRelatedCustomerEntry());
                $em->merge($data);
                $em->flush();
                $this->addFlash('success','Thank you for Your Order!');

                //Send An Email
                $message = (new \Swift_Message('Hello Email'))
                    ->setFrom('send@example.com') //todo:Change email
                    ->setTo($this->getUser()->getEmail())
                    ->setBody(
                        $this->renderView(
                        // app/Resources/views/Emails/registration.html.twig
                            'email/product_order_confirmation.email.twig',
                            array('name' => $this->getUser(), 'data' => $data)
                        ),
                        'text/html'
                    );
                $mailer->send($message);

                //clear out session so it is not used again
                $session->clear();
                return $this->render('user_dashboard/order_product_overview.html.twig',array( 'data' => $data, 'tierDiscount'=>$productTierDiscount));

            }

            if ($change->isClicked()) {
                return $this->redirectToRoute('order_product', array('productCategory' => $data->getRelatedProductCategory(), 'amount' => $data->getOrderedAmount(), 'comment'=> $data->getRelatedOrder()->getComment()));
            }
        }//if the user wants to change the order
        return $this->render('user_dashboard/order_product_confirmation.html.twig',
            array('data' => $data, 'form' => $builder->createView(),'tierDiscount'=>$productTierDiscount) );
    }

    /**
     * @Route("/customer/dashboard/{slug1}/{slug}", name="customer_dashboard")
     * @param Request $request
     * @param string|null $slug
     * @param string|null $slug1
     * @return Response
     * @Security("has_role('ROLE_CUSTOMER')")
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function customerDashboardView(Request $request,string $slug = null,string $slug1 = null): Response
    {
        $newOrderStatus = [];
        $deliveredOrderNew = [];
        $partialOrderNew = [];
        $oldOrderStatus = [];
        $deliveredOrderOld = [];
        $partialOrderOld = [];
        $newOrderedProductCategories = [];
        $oldOrderedProductCategories = [];
        $pendingOrderNew = [];
        $pendingOrderOld = [];
        $dateTimeNow = new DateTime('now');
        $em = $this->getDoctrine()->getManager();

        $orders = $em->getRepository(Order::class)->findBy(array('relatedCustomer' => $this->getUser()->getRelatedCustomerEntry()), array('id' => 'DESC'));
        // separating old orders from new depending on the delivery date
        foreach ($orders as $order)
        {
            $deliveryDate = $order->getTargetTime();
            if ($deliveryDate >= $dateTimeNow)
            {   //Sort the Data for New Orders
                $newOrderedProductCategory = $order->getRelatedOrderedCategorys();
                if ($newOrderedProductCategory !== null){
                    $newOrderedProductCategories[] = $newOrderedProductCategory;
                    $deliveredAmount = $newOrderedProductCategory->getDeliveredAmount();
                    $orderedAmount = $newOrderedProductCategory->getOrderedAmount();
                    $newOrderStatus[$newOrderedProductCategory->getRelatedOrder()->getId()] = $this->getOrderStatus($deliveredAmount, $orderedAmount);
                    if ($deliveredAmount >= $orderedAmount){
                        $deliveredOrderNew[] = $newOrderedProductCategory;
                    } elseif($deliveredAmount < $orderedAmount && $deliveredAmount > 0)  {
                        $partialOrderNew[] = $newOrderedProductCategory;
                    } elseif ($deliveredAmount === 0){
                        $pendingOrderNew[] = $newOrderedProductCategory;
                    }
                }
            } else{
                //Sort the Data for Old Orders
                $oldOrderedProductCategory = $order->getRelatedOrderedCategorys();
                if ($oldOrderedProductCategory!== null){
                    $oldOrderedProductCategories[] = $oldOrderedProductCategory;
                    $deliveredAmount= $oldOrderedProductCategory->getDeliveredAmount();
                    $orderedAmount= $oldOrderedProductCategory->getOrderedAmount();
                    $oldOrderStatus[$oldOrderedProductCategory->getRelatedOrder()->getId()] = $this->getOrderStatus($deliveredAmount,$orderedAmount);
                    if ($deliveredAmount >= $orderedAmount){
                        $deliveredOrderOld[] = $oldOrderedProductCategory;
                    } elseif($deliveredAmount < $orderedAmount && $deliveredAmount > 0)  {
                        $partialOrderOld[] = $oldOrderedProductCategory;
                    }elseif ($deliveredAmount === 0){
                        $pendingOrderOld[] = $oldOrderedProductCategory;
                    }
                }
            }
        }


        // Switch views depending on which type is selected
        //shows all the orders
        if ($slug === null || $slug === 'all' ) {
            $orderProductCategoryNew = $newOrderedProductCategories;
            $orderProductCategoryOld = $oldOrderedProductCategories;
        } elseif ($slug === 'pending'){
            $orderProductCategoryNew = $pendingOrderNew;
            $orderProductCategoryOld = $pendingOrderOld;
        } elseif ($slug === 'delivered'){
            $orderProductCategoryNew = $deliveredOrderNew;
            $orderProductCategoryOld = $deliveredOrderOld;
        } elseif ($slug === 'partial'){
            $orderProductCategoryNew = $partialOrderNew;
            $orderProductCategoryOld = $partialOrderOld;
        } else{ //inCase an unforeseen problem happens
            $orderProductCategoryNew = $newOrderedProductCategories;
            $orderProductCategoryOld = $oldOrderedProductCategories;
        }

        //Pagination code
        $paginate = $this->get('knp_paginator');

        if ($slug1 === 'old') {
            $paginate = $paginate->paginate($orderProductCategoryOld, $request->query->getInt('page',1));
            return $this->render('user_dashboard/customer_dashboard.html.twig',
                array('status' => $oldOrderStatus, 'pagination' => $paginate));
        }
        $paginate = $paginate->paginate($orderProductCategoryNew, $request->query->getInt('page',1));
        return $this->render('user_dashboard/customer_dashboard.html.twig',
            array('status' => $newOrderStatus, 'pagination'=> $paginate) );
    }


    /**
     * @Route("/order/product_delete/{id}", name= "order_delete")
     * @param $id
     * @return RedirectResponse|Response
     * @Security("has_role('ROLE_CUSTOMER')")
     * @ParamDecryptor(params={"id"})
     * @throws \LogicException
     */
    public function deleteOrderAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $orderedProductCategory = $em->getRepository(OrderedProductCategory::class)->find($id);
        if ($orderedProductCategory){
            $order = $orderedProductCategory->getRelatedOrder();
            $em->remove($orderedProductCategory);
            $em->remove($order);
            $em->flush();
        }
        return $this->redirectToRoute('customer_dashboard');
    }

    /**
     * function to check what type of status is the order under
     *
     * @param $deliveredAmount
     * @param $orderedAmount
     * @return string
     */
    private function getOrderStatus($deliveredAmount, $orderedAmount ): string
    {
        if ($deliveredAmount >= $orderedAmount){
            $orderStatus = 'Delivered';
        } elseif($deliveredAmount < $orderedAmount && $deliveredAmount > 0)  {
            $orderStatus = 'Partial';
        } elseif ($deliveredAmount === 0) {
            $orderStatus = 'Pending';
        } else{
            $orderStatus = 'Unknown';
        }
        return $orderStatus;
    }

    /**
     * Simple function to insure no order is created on the same date
     *
     * @param DateTime $targetTime
     * @return bool
     */
    private function checkIfSameOrderDate(DateTime $targetTime): bool
    {
        $orders = $this->getDoctrine()->getRepository(Order::class)->findBy(array('relatedCustomer'=>$this->getUser()->getRelatedCustomerEntry()));
        foreach ($orders as $order){
            $timeTest = $order->getTargetTime();
            if ($timeTest == $targetTime){
                return true;
            }
        }
        return false;
    }
}