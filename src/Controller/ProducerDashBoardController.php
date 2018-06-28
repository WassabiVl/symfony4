<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 22/01/2018
 * Time: 09:39
 */

namespace App\Controller;

use App\Entity\Batch;
use App\Entity\OrderedProducts;
use App\Entity\Producer;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ProducerDashBoardController extends Controller
{

    /**
     * @Route("/producer/dashboard/{slug}", name="producer_dashboard")
     * @Security("has_role('ROLE_PRODUCER')")
     * @param Request $request
     * @param string|null $slug
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function showAction(Request $request, string $slug = null ): Response
    {
        $d3 = 0;
        $d3old = 0;
        $batchCollection =0;
        //Variables to start with
        /** @var Producer $producer */
        $producer = $this->getUser()->getRelatedProducerEntry();
        $relatedBatches = $producer->getRelatedBatches();
        $dateTimeNow = new DateTime('now');
        $dateTime30Days = new DateTime('-30 days');
        $orderNew =[]; //array for collection new orders
        $orderOld =[]; //array for collection old orders

//        Iterate through the batches and find the Order related to them
        /** @var Batch $relatedBatch */
        foreach ($relatedBatches as $batchId=> $relatedBatch){
            $batchCollection += $relatedBatch->getBatchAmount();
            /** @var OrderedProducts $relatedOrderedProduct */
            foreach ($relatedBatch->getRelatedProduct()->getRelatedOrderedProduct() as $relatedOrderedProduct) {
                $orderedProductCategory = $relatedOrderedProduct->getOrderedProductCategory();
                if ($orderedProductCategory !== null) {
                    $relatedOrder = $orderedProductCategory->getRelatedOrder();
                    if ($relatedOrder !== null) {
                        $targetTime = $relatedOrder->getTargetTime();
                        if ($targetTime >= $dateTimeNow) {
                            $orderNew[] = $relatedOrderedProduct;
                            $d3 += $relatedOrderedProduct->getAmount();
                        } elseif ($targetTime < $dateTimeNow) {
                            $orderOld[] = $relatedOrderedProduct;
                            if ($targetTime >= $dateTime30Days){
                                $d3old += $relatedOrderedProduct->getAmount();
                            }
                        }
                    }
                }
            }

        }
        //calculations for D3 pie graph
        $d3 /=  $batchCollection;
        $d3 *=  100;
        $d3old /=  ($batchCollection*30);
        $d3old *=  100;

        //Enable Pagination of the data retrieved using the KNP_Pagination Bundle
        $paginator = $this->get('knp_paginator');
        if($slug==='old'){
            $paginator = $paginator->paginate($orderOld, $request->query->getInt('page',1));
            return $this->render('user_dashboard/producer_dashboard.html.twig', array( 'pagination'=> $paginator, 'd3' => $d3, 'd3old' => $d3old));
        }
        $paginator = $paginator->paginate($orderNew, $request->query->getInt('page',1));
        return $this->render('user_dashboard/producer_dashboard.html.twig', array( 'pagination'=> $paginator, 'd3' => $d3, 'd3old' => $d3old));
    }

}