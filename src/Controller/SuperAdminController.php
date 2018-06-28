<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 15/03/2018
 * Time: 09:57
 */

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Batch;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderedProductCategory;
use App\Entity\OrderedProducts;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swift_Attachment;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Swift_Mailer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Security("has_role('ROLE_ADMIN' or 'ROLE_SUPER_ADMIN')")
 */
class SuperAdminController extends AlgoController
{

    private function getTestProfit($batchInfo){
        $profit = 0;
        foreach ($batchInfo as $batch)
        {
            if ($batch['usedAmount'] !== 0){
                $profit += $batch['profit'];
            }
        }
        return $profit;
    }
    /**
     * @Security("has_role('ROLE_ADMIN' or 'ROLE_SUPER_ADMIN')")
     * @Route("/super_admin", name="super_admin")
     * @param SessionInterface $session
     * @return Response|RedirectResponse
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     * @throws \LogicException
     * @throws \OutOfBoundsException
     */
    public function superAdminController(SessionInterface $session)
    {
        $bestList = null;
        $bestBatch = null;
        $bestUnOrdered = null;
        parent::originalAlgorithm();
        $bestList = parent::getOptimalListToOrder();
        $bestBatch = parent::getBatchInfo();
        $bestUnOrdered = parent::getRemainingList();
        $bestProfit = $this->getTestProfit($bestBatch);

        parent::setOptimalListToOrder(array());
        parent::setBatchInfo(array());
        parent::setRemainingList(array());

        parent::comboAlgorithm(true,false);
        $newList = parent::getOptimalListToOrder();
        $newBatch = parent::getBatchInfo();
        $newUnOrdered = parent::getRemainingList();
        $newProfit = $this->getTestProfit($newBatch);

        if ($newProfit>$bestProfit){
            $bestList = $newList;
            $bestBatch = $newBatch;
            $bestUnOrdered = $newUnOrdered;
            $bestProfit = $newProfit;
        }

        parent::setOptimalListToOrder(array());
        parent::setBatchInfo(array());
        parent::setRemainingList(array());

        parent::comboAlgorithm(true,true);
        $newList = parent::getOptimalListToOrder();
        $newBatch = parent::getBatchInfo();
        $newUnOrdered = parent::getRemainingList();
        $newProfit = $this->getTestProfit($newBatch);

        if ($newProfit>$bestProfit){
            $bestList = $newList;
            $bestBatch = $newBatch;
            $bestUnOrdered = $newUnOrdered;
            $bestProfit = $newProfit;
        }

        parent::setOptimalListToOrder(array());
        parent::setBatchInfo(array());
        parent::setRemainingList(array());

        parent::comboAlgorithm(false,false);
        $newList = parent::getOptimalListToOrder();
        $newBatch = parent::getBatchInfo();
        $newUnOrdered = parent::getRemainingList();
        $newProfit = $this->getTestProfit($newBatch);

        if ($newProfit>$bestProfit){
            $bestList = $newList;
            $bestBatch = $newBatch;
            $bestUnOrdered = $newUnOrdered;
            $bestProfit = $newProfit;
        }
        parent::setOptimalListToOrder(array());
        parent::setBatchInfo(array());
        parent::setRemainingList(array());

        parent::comboAlgorithm(false,true);
        $newList = parent::getOptimalListToOrder();
        $newBatch = parent::getBatchInfo();
        $newUnOrdered = parent::getRemainingList();
        $newProfit = $this->getTestProfit($newBatch);

        if ($newProfit>$bestProfit){
            $bestList = $newList;
            $bestBatch = $newBatch;
            $bestUnOrdered = $newUnOrdered;
        }

        $session->set('optimizedList', $bestList);
        $session->set('batchInfo', $bestBatch);
        $session->set('unOrderedList', $bestUnOrdered);
        return $this->redirectToRoute('super_admin_confirm');

    }


    /**
     * @Security("has_role('ROLE_ADMIN' or 'ROLE_SUPER_ADMIN')")
     * @Route("/super_admin/confirm", name="super_admin_confirm")
     * @param SessionInterface $session
     * @param Request $request
     * @param Swift_Mailer $mailer
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function displayData(SessionInterface $session, Request $request, Swift_Mailer $mailer)
    {
        $optimizedList = $session->get('optimizedList');
        $batchInfo = $session->get('batchInfo');
        $unOrderedList = $session->get('unOrderedList');
        $builder = $this->createFormBuilder()
            ->add('AcceptOrders', SubmitType::class)
            ->add('AcceptChanges', SubmitType::class);
        $em = $this->getDoctrine()->getManager();
        $arrayCollectionOrders = new ArrayCollection();
        $data =[];

        /** @var array $optimizedList */
        foreach ($optimizedList as $batchIdKey => $orders){
            $batch = $em->getRepository(Batch::class)->find($batchIdKey);
            $batchInfo[$batchIdKey][] = $batch;
            /** @var array $orders */
            foreach ($orders as $orderIdKey => $value){
                $order = $em->getRepository(OrderedProductCategory::class)->find($orderIdKey);
                $bool = null;
                if ($order->getRelatedOrder()->getIsFixed()) {
                    $bool = true;
                }
                $data[] = [$batchIdKey,$order,$value];
                $builder->add($orderIdKey,ChoiceType::class,array(
                    'choices'=> array(
                        'none'=> null,
                        'fixed'=> true,
                        'rejected'=> false
                    ),
                    'data' => $bool
                ));
            }
        }
        /** @var array $unOrderedList */
        foreach ($unOrderedList as $orderIdKey => $value){
            $unOrder = $em->getRepository(OrderedProductCategory::class)->find($orderIdKey);
            $arrayCollectionOrders->add($unOrder);
            $builder->add($orderIdKey,ChoiceType::class,array(
                'choices'=> array(
                    'none'=> null,
                    'fixed'=> true,
                    'rejected'=> false
                ),
                'data' => false
            ));
        }
        $form = $builder->getForm();
        $form->handleRequest($request);

        /**
         * @var SubmitButton $acceptChanges
         * @var SubmitButton $acceptOrders
         */
        $acceptChanges = $form->get('AcceptChanges');
        $acceptOrders = $form->get('AcceptOrders');
        if ($form->isSubmitted() && $form->isValid()){
            //form that changes the order type {null, rejected, fixed}
            if($acceptChanges->isClicked()){
                foreach ($form->getData() as $orderKey=> $data){
                    $orderedProductCategory = $em->getRepository(OrderedProductCategory::class)->find($orderKey);
                    /** @var Order $order */
                    $order = $orderedProductCategory->getRelatedOrder();
                    if ($data === true && $order){
                        $order->setIsFixed(true);
                        $order->setIsRejected(false);
                    } else if($data === false && $order){
                        $order->setIsRejected(true);
                        $order->setIsFixed(false);
                    } else if ($data === null && $order) {
                        $order->setIsFixed(false);
                        $order->setIsRejected(false);
                    }
                    $em->persist($order);
                }
                $em->flush();
                return $this->redirectToRoute('super_admin');
            }
            if ($acceptOrders->isClicked()){
                $this->justInCase(); //delete any left over files from the tmp directory of an error happened
                $this->sendRejectionsEmail($arrayCollectionOrders,$mailer);
                /** @var array $optimizedList */
                foreach ($optimizedList as $batchIdKey => $orders) {
                    $orderSent=[];
                    if ($orders!==null && !empty($orders)){
                        $batch = $em->getRepository(Batch::class)->find($batchIdKey);
                        if ($batch !== null) {
                            /** @var array $orders */
                            foreach ($orders as $orderIdKey => $value) {
                                $orderedProductCategory = $em->getRepository(OrderedProductCategory::class)->find($orderIdKey);
                                if ($orderedProductCategory !== null){
                                    $orderedProduct = New OrderedProducts();
                                    $orderSent[] = $orderedProductCategory->getRelatedOrder();
                                    $orderedProduct->setOrderedProductCategory($orderedProductCategory);
                                    $orderedProduct->setAmount($value['amountToProduce']);
                                    $orderedProduct->setProducerAddress($batch->getRelatedProducer()->getPickUpAddress()->__toString());
                                    $orderedProduct->setRelatedProduct($batch->getRelatedProduct());
                                    $em->persist($orderedProduct);
                                    $orderedProductCategory->setRelatedOrderedProduct($orderedProduct);
                                    $orderedProductCategory->getRelatedOrder()->setIsOptimized(true);
                                    $em->persist($orderedProductCategory);
                                    $em->flush();
                                    $this->sendEmailToCustomer($orderedProductCategory->getRelatedOrder(), $value['amountOrdered'],$mailer);
                                }
                            }
                            $this->sendEmailToProducerAndCarrier($batch, $orderSent, $mailer);
                        }
                    }
                }
                $this->cleanup($mailer,$session);
                return $this->redirectToRoute('admin');
            }
        }

        return $this->render('algorithm/algo_run.html.twig', array(
            'datas' => $data,
            'batchs'=> $batchInfo,
            'unOrdereds' => $arrayCollectionOrders,
            'form'=>$form->createView()
        ));
    }

    /***********************************************************************************************************************
     * Email swift Mailer sending function
     * 1) send to the producer and Carrier their list with CSv file Attached
     * 2) send email to customer if accepted or changed amount
     * 3) send email to customer if rejected
     ***********************************************************************************************************************/

    /**
     * 1) Send Email to Producer and Carrier
     *
     * @param Batch $batch
     * @param Order[] $orderSent
     * @param Swift_Mailer $mailer
     * @throws \LogicException
     */
    private function sendEmailToProducerAndCarrier(Batch $batch, $orderSent,Swift_Mailer $mailer):void
    {
        $subjectProducer = $this->get('translator')->trans('Orders for Tomorrow - Producer');
        $subjectCarrier = $this->get('translator')->trans('Orders for Tomorrow - Carrier');
        /** @var Account $admin */
        $admin =$this->getUser()->getEmail();
        $producerFile = $this->createProducerCSVFile($batch,$orderSent);
        $carrierFile = $this->createCarrierCSVFile($batch,$orderSent);
        // the message to the producer
        $messageProducer = (new \Swift_Message($subjectProducer))
            ->setFrom($admin)
            ->setTo($batch->getRelatedProducer()->getRelatedAccount()->getEmail())
            ->setBody(
                $this->renderView('algorithm/algo_email_producer.html.twig'
                    ,array('name'=>$batch->getRelatedProducer(), 'batch' => $batch, 'orders' => $orderSent))
                , 'text/html'
            )
            ->attach(Swift_Attachment::fromPath($producerFile))
        ;
        $mailer->send($messageProducer);
        $carrier = $batch->getRelatedProducer()->getRelatedCarrier();
        if ($carrier && parent::isUserActive($carrier->getRelatedAccount())){
            //the message to the carrier
            $messageCarrier = (new \Swift_Message($subjectCarrier))
                ->setFrom($admin)
                ->setTo($batch->getRelatedProducer()->getRelatedCarrier()->getRelatedAccount()->getEmail())
                ->setBody(
                    $this->renderView('algorithm/algo_email_carrier.html.twig'
                        ,array(
                            'name'=>$batch->getRelatedProducer()->getRelatedCarrier(),
                            'batch' => $batch,
                            'orders' => $orderSent,
                            'producer'=>$batch->getRelatedProducer()))
                    , 'text/html'
                )
                ->attach(Swift_Attachment::fromPath($carrierFile));
            $mailer->send($messageCarrier);
        } else { //Email the Admin Saying the producer does not have an assigned carrier
            $messageAdmin = (new \Swift_Message($subjectCarrier))
                ->setFrom($admin)
                ->setTo($admin)
                ->setBody( 'Producer: ' . $batch->getRelatedProducer()->__toString(). ' does not have a valid Carrier assigned')
                ->attach(Swift_Attachment::fromPath($carrierFile));

            $mailer->send($messageAdmin);
        }
    }

    /**
     * 1-a) Create the CVS file for producer to be uploaded
     * @param Batch $batch
     * @param Order[] $orderSents
     * @return string
     */
    private function createProducerCSVFile(Batch $batch, $orderSents): string
    {
        $encoders = array(new CsvEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $fileName = 'orderConfirmation' . $batch->getId(). '_' .$batch->getRelatedProducer()->getId().'.csv';
        $directory = $this->getParameter('doc_directory').DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR;
        $fileNameFullPath = $directory.$fileName;
        $csvOrders= [];
        foreach ($orderSents as $orderSent){
            $csvOrders[] = [
                'Order #' =>$orderSent->getId(),
                'Customer' => $orderSent->getRelatedCustomer()->__toString(),
                'Customer Address'=> $orderSent->getRelatedCustomer()->getShippingAddress()->__toString(),
                'Delivery Time' => $orderSent->getDateOrdered()->format('Y-m-d H:i:s'),
                'Amount to be Produced' => $orderSent->getRelatedOrderedCategorys()->getRelatedOrderedProduct()->getAmount() .'Mbq',
            ];
        }
        $csvData = [
            'Batch'=> 'Batch '.$batch->getId(),
            'End Time'=> $batch->getDailyEndTime()->format('H:i'),
            'Orders'=> [
                $csvOrders
            ]
        ];
        $csvSerialized = $serializer->serialize($csvData,'csv');
        file_put_contents($fileNameFullPath,$csvSerialized);
        return $fileNameFullPath;

    }

    /**
     * 1-b) Create the CVS file for Carrier to be uploaded
     * @param Batch $batch
     * @param Order[] $orderSents
     * @return string
     */
    private function createCarrierCSVFile(Batch $batch, $orderSents): string
    {
        $encoders = array(new CsvEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $fileName = 'orderConfirmation' . $batch->getId(). '_' .$batch->getRelatedProducer()->getId(). '_'.'.csv';
        $directory = $this->getParameter('doc_directory').DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR;
        $fileNameFullPath = $directory.$fileName;
        $csvOrders= [];
        foreach ($orderSents as $orderSent){
            $csvOrders[] = [
                'Order #' =>$orderSent->getId(),
                'Customer' => $orderSent->getRelatedCustomer()->__toString(),
                'Customer Address'=> $orderSent->getRelatedCustomer()->getShippingAddress()->__toString(),
                'Delivery Time' => $orderSent->getDateOrdered()->format('Y-m-d H:i:s'),
                'Amount to be Produced' => $orderSent->getRelatedOrderedCategorys()->getRelatedOrderedProduct()->getAmount() .'Mbq',
            ];
        }
        $csvData = [
            'Batch'=> 'Batch '.$batch->getId(),
            'End Time'=> $batch->getDailyEndTime()->format('H:i'),
            'Producer Name' => $batch->getRelatedProducer()->__toString(),
            'Producer Address' => $batch->getRelatedProducer()->getPickUpAddress()->__toString(),
            'Orders'=> [
                $csvOrders
            ]
        ];
        $csvSerialized = $serializer->serialize($csvData,'csv');
        file_put_contents($fileNameFullPath,$csvSerialized);
        return $fileNameFullPath;

    }

    /**
     * 2) Email the customer with success ordering
     * @param Order $order
     * @param float $newValueToOrder
     * @param Swift_Mailer $mailer
     * @throws \Exception
     */
    private function sendEmailToCustomer(Order $order, $newValueToOrder, Swift_Mailer $mailer):void
    {
        $fullFilePathAndName = $this->createOrderConfirmationPDF($order->getRelatedOrderedCategorys(), $newValueToOrder);
        /** @var Account $admin */
        $admin =$this->getUser()->getEmail();
        $em = $this->getDoctrine()->getManager();
        $message =null;
        $translated = $this->get('translator')->trans('Your order has been reduced to:');
        $translated1 = $this->get('translator')->trans('Mbq by the Admin');
        $subject = $this->get('translator')->trans('Orders for Tomorrow - Customer');
        /** @var Customer $customer */
        $customer = $order->getRelatedCustomer();
        // if the requested order matches the value the algorithm gave, means that the amount was not altered
        if ($order->getRelatedOrderedCategorys()->getOrderedAmount() === $newValueToOrder){
            $message = (new \Swift_Message($subject))
                ->setFrom($admin)
                ->setTo($customer->getRelatedAccount()->getEmail())
                ->setBody(
                    $this->renderView('algorithm/algo_email_customer.html.twig'
                        ,array('name'=>$customer, 'order' => $order))
                    , 'text/html'
                )
                ->attach(Swift_Attachment::fromPath($fullFilePathAndName))
            ;
        }
        // the amount was altered
        else{ // if new amount has been set by algorithm
            $message = (new \Swift_Message($subject))
                ->setFrom($admin)
                ->setTo($customer->getRelatedAccount()->getEmail())
                ->setBody(
                    $this->renderView('algorithm/algo_email_customer_alt.html.twig'
                        ,array('name'=>$customer, 'order' => $order, 'newAmount' => $newValueToOrder))
                    , 'text/html'
                )
                ->attach(Swift_Attachment::fromPath($fullFilePathAndName))
            ;
            $order->setFlag($translated . $newValueToOrder. $translated1.'');
        }
        // saving the file to the repository
        $fileName = 'orderConfirmation' . $order->getId(). '_' .$customer->getId().'.pdf';
        $file = new UploadedFile($fullFilePathAndName,$fileName);
        $order->setOrderConformation($this->get(DocumentController::class)->persistFile($file,null,'OrderConfirmation'));
        $em->persist($order);
        $em->flush();

        $mailer->send($message);
    }

    /**
     * @param OrderedProductCategory $orderedProductCategory
     * @param float $newValueToOrder
     * @return string
     */
    private function createOrderConfirmationPDF(OrderedProductCategory $orderedProductCategory, $newValueToOrder): string
    {
        $customer = $orderedProductCategory->getRelatedOrder()->getRelatedCustomer();
        $html = null;
        $fileName = 'orderConfirmation' . $orderedProductCategory->getId(). '_' .$customer->getId().'.pdf';
        if ($orderedProductCategory->getOrderedAmount() === $newValueToOrder){
            $html = $this->renderView(':algorithm:order_conformation_pdf_template.html.twig', array('data' => $orderedProductCategory, 'customer'=> $customer));
        } else{
            $html = $this->renderView(':algorithm:order_confirmation_pdf_template_alt.html.twig', array('data' => $orderedProductCategory, 'customer'=> $customer,'newAmount'=>$newValueToOrder));
        }
        $knpSnappyBundle = $this->get('knp_snappy.pdf');
        $fullFilePathAndName = $this->getParameter('doc_directory').DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$fileName;
        $knpSnappyBundle->generateFromHtml($html, $fullFilePathAndName);
        return $fullFilePathAndName;
    }

    /**
     * @param ArrayCollection $orders
     * @param Swift_Mailer $mailer
     * @throws \LogicException
     */
    private function sendRejectionsEmail(ArrayCollection $orders, Swift_Mailer $mailer): void
    {
        $em = $this->getDoctrine()->getManager();
        $translated = $this->get('translator')->trans('Your order has been rejected');
        $subject = $this->get('translator')->trans('Orders for Tomorrow');
        $admin =$this->getUser()->getEmail();
        /**
         * @var $orders OrderedProductCategory[]
         * @var $order OrderedProductCategory
         */
        foreach ($orders as $order){
            /** @var Customer $customer */
            $customer = $order->getRelatedOrder()->getRelatedCustomer();
            $message = (new \Swift_Message($subject))
                ->setFrom($admin)
                ->setTo($customer->getRelatedAccount()->getEmail())
                ->setBody(
                    $this->renderView('algorithm/algo_email_customer_rejected.html.twig'
                        ,array('name'=>$customer, 'order' => $order->getId()))
                    , 'text/html'
                )
            ;
            $mailer->send($message);
            $order->getRelatedOrder()->setFlag($translated);
            $order->getRelatedOrder()->setIsOptimized(false);
            $order->getRelatedOrder()->setIsRejected(true);
            $em->persist($order);
        }
        $em->flush();
    }



    /*******************************************************************************************************************
     * Tmp File Delete and Email spooling sending
     * @param Swift_Mailer $mailer
     * @param SessionInterface $session
     * @return void
     ******************************************************************************************************************/
    private function cleanup(Swift_Mailer $mailer, SessionInterface $session): void
    {
        $mailer->getTransport()->start();
        $directory = $this->getParameter('doc_directory').DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR ;
        if (is_dir($directory) && $dh = opendir($directory)) {
            while (($file = readdir($dh)) !== false) {
                $fullFile = $directory . $file;
                register_shutdown_function(function() use ($fullFile){
                    if (file_exists($fullFile)) {
                        unlink($fullFile);
                    }
                });
            }
            closedir($dh);
        }
        $session->clear();
        $mailer->getTransport()->stop();

    }

    private function justInCase(): void
    {
        $directory = $this->getParameter('doc_directory').DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'*' ;
        foreach(glob($directory) as $file){ // iterate files
            if(is_file($file)) {
                unlink($file); // delete file
            }
        }
    }

    /*******************************************************************************
     * Functions to be deleted from Dev to production
     *****************************************************************************/


    /**
     * TODO:Delete Before moving to production
     * @throws \LogicException
     */
    private function realEndDatesOfBatches():void{
        $em = $this->getDoctrine()->getManager();
        $batch = $em->getRepository(Batch::class);
        $batch1 = $batch->find(1);
        $batch1->setDailyEndTime(new \DateTime('3:30 am'));
        $batch2 = $batch->find(2);
        $batch2->setDailyEndTime(new \DateTime('5:30 am'));
        $batch3 = $batch->find(3);
        $batch3->setDailyEndTime(new \DateTime('5 am'));
        $em->persist($batch1);
        $em->persist($batch2);
        $em->persist($batch3);
        $orders = $em->getRepository(Order::class)->findAll();
        foreach ($orders as $order){
            $times = array(new \DateTime('tomorrow 9am'),new \DateTime('tomorrow 8am'));
            $order->setTargetTime($times[array_rand($times,1)]);
            $em->persist($order);
        }
        $em->flush();
    }

    /**
     * @Route("/fake_super_admin", name="fake_super_admin")
     * @param Request $request
     * @param KernelInterface $kernel
     * @param SessionInterface $session
     * @return Response|RedirectResponse
     * @throws \Exception
     */
    public function fakeSuperAdmin(Request $request, KernelInterface $kernel, SessionInterface $session)
    {

        // Fixture Run /////////////////////////////////////////////////////////////////////////////////
        $content  ='';
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $input = new ArrayInput(array(
            'command' => 'app:fixturesReload',
        ));
        $output = new BufferedOutput();

        // Button Form View TO Allow calling Functions from the front end/////////////////////////////////////////////
        $builder= $this->createFormBuilder()
            ->add('Run Fixtures', SubmitType::class)
            ->add('Set Real Batch EndTime and all order delivery dates for tomorrow 8 or 9 am', SubmitType::class)
            ->add('Run Original Algorithm', SubmitType::class)
            ->add('Run Reverse Original Algorithm', SubmitType::class)
            ->add('High Batch Count Over Least Order Cost Algorithm', SubmitType::class)
            ->add('Low Batch Count Over Least Order Cost Algorithm', SubmitType::class)
            ->add('High Batch Count Over High Order Cost Algorithm', SubmitType::class)
            ->add('low Batch Count Over High Order Cost Algorithm', SubmitType::class)
            ->getForm();
        $builder->handleRequest($request);
        /**
         * @var $runFixturesButton SubmitButton
         * @var $canBeDelivered SubmitButton
         * @var $runAlgo SubmitButton
         * @var $runReverseAlgo SubmitButton
         * @var $runSecondAlgo SubmitButton
         * @var $runThirdAlgo SubmitButton
         * @var $runForthAlgo SubmitButton
         * @var $runFifthAlgo SubmitButton
         */
        $runFixturesButton = $builder->get('Run Fixtures');
        $canBeDelivered = $builder->get('Set Real Batch EndTime and all order delivery dates for tomorrow 8 or 9 am');
        $runAlgo = $builder->get('Run Original Algorithm');
        $runReverseAlgo = $builder->get('Run Reverse Original Algorithm');
        $runSecondAlgo = $builder->get('High Batch Count Over Least Order Cost Algorithm');
        $runThirdAlgo = $builder->get('Low Batch Count Over Least Order Cost Algorithm');
        $runForthAlgo = $builder->get('High Batch Count Over High Order Cost Algorithm');
        $runFifthAlgo = $builder->get('low Batch Count Over High Order Cost Algorithm');

        if ($builder->isSubmitted() && $builder->isValid()) {
            if ($runFixturesButton->isClicked()) {
                $application->run($input, $output);
                $content = $output->fetch();
                return $this->render('algorithm/fake_super_admin.html.twig', array(
                    'form' => $builder->createView(),
                    'content'=> $content
                ));
            }
            if ($canBeDelivered->isClicked()) {
                $this->realEndDatesOfBatches();
                return $this->render('algorithm/fake_super_admin.html.twig', array(
                    'form' => $builder->createView(),
                    'content'=> $content
                ));
            }
            if ($runAlgo->isClicked()) {
                parent::originalAlgorithm();
            }
            if ($runReverseAlgo->isClicked()) {
                parent::originalReverseAlgorithm();
            }
            if ($runSecondAlgo->isClicked()) {
                parent::comboAlgorithm(true, false);
            }
            if ($runThirdAlgo->isClicked()) {
                parent::comboAlgorithm(false, false);
            }
            if ($runForthAlgo->isClicked()) {
                parent::comboAlgorithm(true, true);
            }
            if ($runFifthAlgo->isClicked()) {
                parent::comboAlgorithm(false, true);
            }
            $optimizedList = parent::getOptimalListToOrder();
            $batchInfo = parent::getBatchInfo();
            $unOrderedList = parent::getRemainingList();
            $session->set('optimizedList', $optimizedList);
            $session->set('batchInfo', $batchInfo);
            $session->set('unOrderedList', $unOrderedList);
            return $this->redirectToRoute('super_admin_confirm');
        }

        return $this->render('algorithm/fake_super_admin.html.twig', array(
            'form' => $builder->createView(),
            'content'=> $content
        ));

    }
}