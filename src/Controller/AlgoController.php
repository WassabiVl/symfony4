<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 26/02/2018
 * Time: 16:24
 *
 * ToDo: add a checking part to remove batch with negative profit value...should increase the functionality of the original algorithm
 *
 */

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Batch;
use App\Entity\BatchLockTime;
use App\Entity\CarrierCost;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderedProductCategory;
use App\Entity\Producer;
use App\Entity\Product;
use App\Entity\ProductSellSizes;
use DateTime;

class AlgoController extends GoogleController
{

    /***************************************************************
     *  The Threshold variable
     *************************************************************
     * @param $batch Batch
     * @return float|int
     */

    private function getThresholdForBatch($batch){
        $buyPrice = $batch->getRelatedProduct()->getBuyPrice();
        $halfLife = $batch->getRelatedProduct()->getHalfLife();
        $batchEndTime = $batch->getDailyEndTime();
        $batchCost = $batch->getBatchCost();

        $discounts = $batch->getRelatedProduct()->getProductCategory()->getRelatedProductSellSizes();
        $maxDiscount = 0;
        foreach ($discounts as $discount){

            if ($discount->getDiscountInPercent() > $maxDiscount) {
                $maxDiscount = $discount->getDiscountInPercent();
            }
        }
        $sellPrice = $batch->getRelatedProduct()->getProductCategory()->getSellPrice();
        $minSellPrice = $sellPrice*(1-$maxDiscount/100);

        $transportationCosts = $batch->getRelatedProducer()->getRelatedCarrierCost();
        $maxTransportationCost = 0;
        foreach ($transportationCosts as $transportationCost){
            if ($transportationCost->getCost() > $maxTransportationCost){
                $maxTransportationCost = $transportationCost->getCost();
            }
        }


        $maxTargetTime = new DateTime('9 am');  //TODO: this value should be recovered from a database source or the threshold will break.

        $halfLifeAmount = $this->halfLifeCalculator($halfLife, $maxTargetTime, $batchEndTime);

        $minAmountToOrder = ($maxTransportationCost+$batchCost)/($minSellPrice-$buyPrice*$halfLifeAmount);
        return $minAmountToOrder;
    }

    /************************************************************************
     *
     * Internal Variables
     *
     *********************************************************************/

    private $remainingList = [];
    private $remainingAmount = [];


    /**
     * @return array
     */
    protected function getRemainingList(): array
    {
        return $this->remainingList;
    }

    /**
     * @param array $remainingList
     */
    protected function setRemainingList($remainingList): void
    {
        $this->remainingList = $remainingList;
    }
    /**
     * @return mixed
     */
    private function getRemainingAmount(): array
    {
        return $this->remainingAmount;
    }

    /**
     * @param mixed $remainingAmount
     */
    private function setRemainingAmount($remainingAmount): void
    {
        $this->remainingAmount = $remainingAmount;
    }


    /************************************************************************
     *
     * Variables that can be accessed outside the class
     *
     *********************************************************************/

    private $optimalListToOrder = [];
    private $batchInfo =[];

    /**
     * @return array
     */
    protected function getOptimalListToOrder(): array
    {
        return $this->optimalListToOrder;
    }

    /**
     * @param array $optimalListToOrder
     */
    protected function setOptimalListToOrder($optimalListToOrder): void
    {
        $this->optimalListToOrder = $optimalListToOrder;
    }


    /**
     * @return array
     */
    protected function getBatchInfo(): array
    {
        return $this->batchInfo;
    }

    /**
     * @param array $batchInfo
     */
    protected function setBatchInfo($batchInfo): void
    {
        $this->batchInfo = $batchInfo;
    }

    /***********************************************************************************************
     *
     * Mathematical Calculation Functions to Standardize the calculation across the Algorithm
     *
     *******************************************************************************************/

    /**
     * @param $time DateTime
     * time is usually parsed in seconds hence the conversion to minutes so that the values we have work
     * @return int
     */
    private function convertToMinutes(DateTime $time):int
    {
        $hour = $time->format('H')*60;
        $minute = $time->format('i')*1;
        return $hour + $minute;
    }

    /**
     * @param DateTime $targetTime
     * @param DateTime $batchEndTime
     * @return int
     */
    private function timeDifference(DateTime $targetTime, DateTime $batchEndTime):int
    {
        $targetTimes =   $this->convertToMinutes($targetTime);
        $batchEndTimes = $this->convertToMinutes($batchEndTime);
        return $targetTimes-$batchEndTimes;
    }

    /**
     * @param $halfLife float
     * @param $targetTime DateTime
     * @param $batchEndTime DateTime
     * @return float
     */
    private function halfLifeCalculator($halfLife, $targetTime, $batchEndTime):float
    {
        $timeDifference =$this->timeDifference($targetTime,$batchEndTime);
        return exp(($timeDifference/$halfLife)*log(2));

    }

    /**
     * @param $amountOrdered float
     * @param $halfLife float
     * @param $targetTime DateTime
     * @param $batchEndTime DateTime
     * @return float
     */
    private function getAmountToBeProduced($amountOrdered, $halfLife, $targetTime, $batchEndTime):float
    {
        $requiredExtraAmount = $this->halfLifeCalculator($halfLife, $targetTime, $batchEndTime);
        $totalAmount = $requiredExtraAmount*$amountOrdered;
        return 1000*ceil($totalAmount/1000);
    }


    /**
     * function to calculate the cost of the order by the cost of the transportation plus the cost to produce the batch in Giga Units
     * @param $transportationCost float
     * @param $batchUnitCost float
     * @param $amountOrdered float
     * @param $halfLife float
     * @param $batchEndTime DateTime
     * @param $targetTime DateTime
     * @return float
     */
    protected function calculateCostOfOrder($transportationCost, $batchUnitCost, $amountOrdered, $halfLife, $batchEndTime, $targetTime):float
    {
        $requiredAmount = $this->getAmountToBeProduced($amountOrdered,$halfLife,$targetTime,$batchEndTime);
        return ceil($transportationCost + $batchUnitCost*$requiredAmount);
    }

    /**
     * @param $producedAmount float
     * @param $halfLife float
     * @param $targetTime DateTime
     * @param $batchEndTime DateTime
     * @return float
     */
    private function getOriginalAmountFromProducedAmount($producedAmount, $halfLife, $targetTime, $batchEndTime):float
    {
        $requiredExtraAmount = $this->halfLifeCalculator($halfLife, $targetTime, $batchEndTime);
        $originalAmount = ceil($producedAmount/$requiredExtraAmount);
        if ($originalAmount > 0){
            return $originalAmount;
        }
        return 0;
    }

    /**
     * @param $transportationCost float
     * @param $batchUnitCost float
     * @param $amountOrdered float
     * @param $halfLife float
     * @param $batchEndTime DateTime
     * @param $targetTime DateTime
     * @param $unitSellPrice float
     * @param $grantedDiscount float|null
     * @return float
     */
    protected function getProfit($transportationCost, $batchUnitCost, $amountOrdered, $halfLife, $batchEndTime, $targetTime, $unitSellPrice, $grantedDiscount): float
    {
        $totalCost =$this->calculateCostOfOrder($transportationCost, $batchUnitCost, $amountOrdered, $halfLife, $batchEndTime, $targetTime);
        $productSellSizes = $this->getDoctrine()->getRepository(ProductSellSizes::class)->findOneBy(array('amount'=> $amountOrdered));
        if ($productSellSizes !== null){
            $discount = $productSellSizes->getDiscountInPercent()/100;}
        else { //usually to handle the remaining orders that where reduced
            $discount =$this->getClosestDiscount($amountOrdered);
        }
        $sellPriceTotal = $amountOrdered*$unitSellPrice*(1-$discount);
        if($grantedDiscount) { //if customer has a discount
            $sellPriceTotal *= (1 - $grantedDiscount);
        }
        return ($sellPriceTotal - $totalCost);

    }

    /**
     * @param $amountOrdered
     * @return float|int
     * @throws \LogicException
     */
    private function getClosestDiscount($amountOrdered) {
        $closest = null;
        $discount = 0;
        $productSellSizes = $this->getDoctrine()->getRepository(ProductSellSizes::class)->findAll();
        foreach ($productSellSizes as $productSellSize) {
            if ($closest === null || abs($amountOrdered - $closest) > abs($productSellSize->getAmount() - $amountOrdered)) {
                $closest = $productSellSize->getAmount();
            }
        }
        $discount1 = $this->getDoctrine()->getRepository(ProductSellSizes::class)->findOneBy(array('amount'=> $closest));
        if ($discount1 !== null){
            $discount = $discount1->getDiscountInPercent()/100;
        }
        return $discount;
    }

    /*********************************************************************************************
     *  Checker Functions With boolean return Values
     *********************************************************************************************/

    /**
     * function to check if the order can be delivered on time from supplier to customer
     * @param $batchEndTime DateTime
     * @param $targetTime DateTime
     * @param $transportationTime int
     * @return bool
     */
    private function canBeShipped($batchEndTime, $targetTime, $transportationTime):bool
    {
        return $this->timeDifference($targetTime, $batchEndTime) >= $transportationTime;

    }

    /**
     * @param $deliveryDate DateTime
     * @return bool
     */
    private function isOrderForTomorrow($deliveryDate):bool
    {
        // taking into account only two delivery times
        $d = new DateTime('tomorrow 7 am');
        $e = new DateTime('tomorrow 10 am');
        return $deliveryDate >= $d and $deliveryDate <= $e;
    }

    /**
     * @param Batch $batch
     * @return bool
     */
    private function isBatchLocked(Batch $batch):bool
    {
        /** @var BatchLockTime $batchLockTime */
        foreach ($batch->getRelatedLockTimes() as $batchLockTime){
            $lockTimeStart = $batchLockTime->getStartTime();
            $lockTimeEnd = $batchLockTime->getEndTime();
            $batchStartTime = new DateTime('today');
            $hour = $batch->getDailyStartTime()->format('H');
            $minute = $batch->getDailyStartTime()->format('i')*1;
            $batchStartTime->setTime($hour,$minute);
            if ($batchStartTime > $lockTimeStart && $batchStartTime < $lockTimeEnd){
                return true;
            }
        }
        return false;
    }

    /**
     * @param Account $account
     * @return bool
     */
    protected function isUserActive(Account $account):bool
    {
        $bool = false;
        if($account->getIsDeleted()){
            return false;
        }
        if ($account->getIsActive() && $account->isEnabled()){
            $bool =true;
            $customer = $account->getRelatedCustomerEntry();
            if ($customer && !$customer->getIsUgValid() ) {
                return false;
            }
        }
        return $bool;

    }

    /**
     * @throws \RuntimeException
     * @throws \LogicException
     */
    private function checkIfCarrierCostExist():void
    {
        $customers = $this->getDoctrine()->getRepository(Customer::class)->findAll();
        $producers = $this->getDoctrine()->getRepository(Producer::class)->findAll();
        foreach ($customers as $customer){
            foreach ($producers as $producer){
                $carrierCost = $this->getDoctrine()->getRepository(CarrierCost::class)->findOneBy(array('relatedProducer'=> $producer, 'relatedCustomer'=> $customer));
                if ($carrierCost === null || empty($carrierCost)){
                    parent::iterateAction();
                    return;
                }
            }
        }
    }

    /************************************************************************************************
     *
     * Helper Functions for the Algorithm that process through to the Initial Array
     * Without losing the Integrity of the Keys and removing any nulls values found
     *
     *******************************************************************************************/


    /**
     * @param $array array
     * @return array
     */
    private function toUse($array): array
    {
        $arr2 =[];
        foreach ($array as $orderIdKey => $batches){
            /** @var array $batches */
            foreach ($batches as $batchKeyId => $cost){
                $arr2[$orderIdKey]= array('batchId' =>$batchKeyId, 'cost' =>$cost );
            }
        }
        return $arr2;
    }

    /**
     * @param $array array
     * @return array
     */
    private function toReturn($array): array
    {
        $array2 =[];
        foreach ($array as $orderIdKey => $value){
            $array2[$orderIdKey][$value['batchId']] = $value['cost'];
        }
        return $array2;
    }

    /**
     * @param $array array
     * @return array
     */
    private function sortByMinCost($array): array
    {
        $array=$this->toUse($array);
        uasort($array,function($a, $b) {
            return $a['cost'] <=> $b['cost'];
        });
        $array = $this->toReturn($array);
        return $array;
    }

    /**
     * @param $array array
     * @return array
     */
    private function sortByMaxCost($array): array
    {
        $array=$this->toUse($array);
        uasort($array,function($a, $b) {
            return $b['cost'] <=> $a['cost'];
        });
        $array = $this->toReturn($array);
        return $array;
    }

    /**
     * @param $arrays array
     * @return array
     */
    private function sortInsideArray($arrays):array
    {
        $arrayNew=[];
        foreach ($arrays as $orderIdKey => $batches){
            if ($batches !== null) {
                asort($batches);
            }
            $arrayNew[$orderIdKey] = $batches;
        }
        return $arrayNew;
    }

    /**
     * @param array $orderBatches
     * @param  int $orderId
     * @return array
     */
    private function removeOrderFromList ($orderBatches, $orderId): array
    {
        $array=[];
        foreach ($orderBatches as $OrderIdKey =>$orderBatch){
            if ($orderId !== $OrderIdKey){
                $array[$OrderIdKey] = $orderBatch;
            }
        }
        return $array;
    }

    /**
     * @param $orderBatch array
     * @param $batchKey int
     * @return array
     */
    private function getLowestOrdersCostForBatch($orderBatch, $batchKey):array
    {
        $array =[];
        foreach ($orderBatch as $orderKey => $batches){
            /** @var array $batches */
            foreach ($batches as $batchId => $cost){
                if ($batchId === $batchKey){
                    $array[$orderKey] = $cost;
                }
            }
        }
        asort($array);
        return $array;
    }
    /**
     * @param $orderBatch array
     * @param $batchKey int
     * @return array
     */
    private function getHighestOrdersCostForBatch($orderBatch, $batchKey):array
    {
        $array =[];
        foreach ($orderBatch as $orderKey => $batches){
            /** @var array $batches */
            foreach ($batches as $batchId => $cost){
                if ($batchId === $batchKey){
                    $array[$orderKey] = $cost;
                }
            }
        }
        arsort($array);
        return $array;
    }
    /*********************************************************************************************
     *
     * Algorithm Steps:
     *        1) Get the List of Orders for tomorrow with their Usable Batches
     *        2) Choose and Get the Minimal Cost of each order
     *        3) Get the Optimal List of Orders to Batches by minimal Cost
     *        4) Handle allocating the remaining amount, if possible
     *
     ****************************************************************************************/

    /**
     * 1) Get the List of Orders for tomorrow with their Usable Batches
     *
     * function to attain list of orders with their cost relative to the batch
     *
     * @return array
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     * @throws \LogicException
     */
    private function getListOfOrderBatches():array
    { //for now we take all the orders, later we set the delivery date to tomorrow
        $this->checkIfCarrierCostExist();
        $array=[];
        $em = $this->getDoctrine()->getManager();
        /** @var OrderedProductCategory $orderedProductCategory */
        foreach ($em->getRepository(OrderedProductCategory::class)->findAll() as $orderedProductCategory) {
            /** @var Order $order */
            $order = $orderedProductCategory->getRelatedOrder();
            if ($order &&$orderedProductCategory->getRelatedOrderedProduct() === null && $this->isUserActive($order->getRelatedCustomer()->getRelatedAccount())){
                $carrierCosts = $em->getRepository(CarrierCost::class)->findBy(array('relatedCustomer' => $order->getRelatedCustomer()));
                $amountOrdered = $orderedProductCategory->getOrderedAmount(); //convert to giga from thou ordered
                $targetTime = $order->getTargetTime();
                if ($this->isOrderForTomorrow($targetTime)) {
                    /** @var CarrierCost $carrierCost */
                    foreach ($carrierCosts as $carrierCost) {
                        $transportationCost = $carrierCost->getCost();
                        $transportationTime = $carrierCost->getEstimatedTime();
                        $batches = $em->getRepository(Batch::class)->findBy(array('relatedProducer' => $carrierCost->getRelatedProducer()));
                        /** @var Batch $batch */
                        foreach ($batches as $batch) {
                            if (!$this->isBatchLocked($batch) && $this->isUserActive($batch->getRelatedProducer()->getRelatedAccount())){
                                $batchEndTime = $batch->getDailyEndTime();
                                if ($this->canBeShipped($batchEndTime, $targetTime, $transportationTime)) {
                                    $halfLife = $batch->getRelatedProduct()->getHalfLife();
                                    $batchUnitCost = $batch->getBatchCost();
                                    $cost = $this->calculateCostOfOrder($transportationCost, $batchUnitCost, $amountOrdered, $halfLife, $batchEndTime, $targetTime);
                                    $array[$orderedProductCategory->getId()][$batch->getId()] = $cost;
                                }
                            }
                        }
                    }
                }
            }
        }
        //sort the array for faster algorithm runs
        $array = $this->sortInsideArray($array);
        return $array;
    }


    /**
     * 2) Choose and Get the Minimal Cost of each order
     *
     * @param $orderBatch array
     * @return array
     */
    private function chooseMinimumCost($orderBatch):array
    {
        $array2 =[];
        if ($orderBatch || !empty($orderBatch)){
            foreach ($orderBatch as $OrderIdKey => $batches){
                $tempValue = null;
                $tempKey = null;
                /** @var array $batches */
                foreach ($batches as $batchIdKey => $cost){
                    if (($tempValue === null && $tempKey === null) || $cost < $tempValue){
                        $tempValue = $cost;
                        $tempKey = $batchIdKey;
                    }
                }
                $array2[$OrderIdKey][$tempKey] = $tempValue;
            }

        }
        return $array2;
    }


    /**
     * 3) Get the Optimal List of Orders to Batches by minimal Cost
     *
     * @param $sortedOrderBatchOfMinCost array
     * @return array
     * @throws \LogicException
     */
    private function getOptimizedOrderList($sortedOrderBatchOfMinCost): array
    {
        $batchAmount = [];
        $toOrderBatch = [];
        $remainingAmount = [];
        $batchInfo =[];
        $em = $this->getDoctrine()->getManager();
        //get the initial Batch Information
        foreach ($em->getRepository(Batch::class)->findAll() as $batch1){
            $batchAmount[$batch1->getId()] = $batch1->getBatchAmount();
            $toOrderBatch[$batch1->getId()] =[];
            $batchInfo[$batch1->getId()] = [];
            $batchInfo[$batch1->getId()]['batchCapacity'] =  $batch1->getBatchAmount();
            $batchInfo[$batch1->getId()]['profit'] = 0;
            $batchInfo[$batch1->getId()]['usedAmount'] = 0;

        }
        // set the orders in their batches as long as they don't go over the capacity
        // Prioritize isFixed First
        foreach ($sortedOrderBatchOfMinCost as $orderKeyId => $batches1){
            $orderedProductCategory = $em->getRepository(OrderedProductCategory::class)->find($orderKeyId);
            if ($orderedProductCategory && !(bool)$orderedProductCategory->getRelatedOrder()->getIsRejected() && $orderedProductCategory->getRelatedOrder()->getIsFixed()){
                /** @var Order $order */
                $order = $orderedProductCategory->getRelatedOrder();
                $amountOrdered =$orderedProductCategory->getOrderedAmount();
                $targetTime =$order->getTargetTime();
                /** @var array $batches1 */
                /** @var int $batchIdKey */
                foreach ($batches1 as $batchIdKey => $cost) {
                    $batch = $em->getRepository(Batch::class)->find($batchIdKey);
                    if ($batch !== null) {
                        /** @var Product $product */
                        $product = $batch->getRelatedProduct();
                        $halfLife = $product->getHalfLife();
                        $batchEndTime = $batch->getDailyEndTime();
                        $amountProduced = $this->getAmountToBeProduced($amountOrdered, $halfLife, $targetTime, $batchEndTime);
                        $batchUnitCost = $batch->getBatchCost();
                        $unitSellPrice = $product->getProductCategory()->getSellPrice();
                        $customer = $order->getRelatedCustomer();
                        $producer = $batch->getRelatedProducer();
                        $transportationCost = $em->getRepository(CarrierCost::class)->findOneBy(array('relatedCustomer' => $customer, 'relatedProducer' => $producer))->getCost();
                        if ($amountProduced <= $batchAmount[$batchIdKey]) {
                            $grantedDiscount = $order->getGrantedDiscount();
                            $batchAmount[$batchIdKey] -= $amountProduced;
                            $remainingAmount[$batchIdKey] = $batchAmount[$batchIdKey];
                            $profit = $this->getProfit($transportationCost, $batchUnitCost, $amountOrdered, $halfLife, $batchEndTime, $targetTime, $unitSellPrice, $grantedDiscount);
                            $toOrderBatch[$batchIdKey][$orderKeyId]['amountToProduce'] = $amountProduced;
                            $toOrderBatch[$batchIdKey][$orderKeyId]['amountOrdered'] = $amountOrdered;
                            $toOrderBatch[$batchIdKey][$orderKeyId]['profit'] = $profit;
                            $batchInfo[$batchIdKey]['usedAmount'] += $amountProduced;
                            $batchInfo[$batchIdKey]['profit'] += $profit;
                            $sortedOrderBatchOfMinCost = $this->removeOrderFromList($sortedOrderBatchOfMinCost, $orderKeyId);
                        }
                    }

                }
            }
        }
        //move to the not Fixed
        foreach ($sortedOrderBatchOfMinCost as $orderKeyId => $batches1){
            $orderedProductCategory =$em->getRepository(OrderedProductCategory::class)->find($orderKeyId);
            /** @var Order $order */
            $order = $orderedProductCategory->getRelatedOrder();
            $rejected = (bool)$order->getIsRejected();
            if ($orderedProductCategory && !$rejected ){
                $amountOrdered =$orderedProductCategory->getOrderedAmount();
                $targetTime =$order->getTargetTime();
                /** @var array $batches1 */
                foreach ($batches1 as $batchIdKey => $cost) {
                    $batch = $em->getRepository(Batch::class)->find($batchIdKey);
                    if ($batch !== null) {
                        /** @var Product $product */
                        $product = $batch->getRelatedProduct();
                        $halfLife = $product->getHalfLife();
                        $batchEndTime = $batch->getDailyEndTime();
                        $amountProduced = $this->getAmountToBeProduced($amountOrdered, $halfLife, $targetTime, $batchEndTime);
                        $batchUnitCost = $batch->getBatchCost();
                        $unitSellPrice = $product->getProductCategory()->getSellPrice();
                        $customer = $order->getRelatedCustomer();
                        $producer = $batch->getRelatedProducer();
                        $transportationCost = $em->getRepository(CarrierCost::class)->findOneBy(array('relatedCustomer' => $customer, 'relatedProducer' => $producer))->getCost();
                        if ($amountProduced <= $batchAmount[$batchIdKey]) {
                            $grantedDiscount = $order->getGrantedDiscount();
                            $batchAmount[$batchIdKey] -= $amountProduced;
                            $remainingAmount[$batchIdKey] = $batchAmount[$batchIdKey];
                            $profit = $this->getProfit($transportationCost, $batchUnitCost, $amountOrdered, $halfLife, $batchEndTime, $targetTime, $unitSellPrice, $grantedDiscount);
                            $toOrderBatch[$batchIdKey][$orderKeyId]['amountToProduce'] = $amountProduced;
                            $toOrderBatch[$batchIdKey][$orderKeyId]['amountOrdered'] = $amountOrdered;
                            $toOrderBatch[$batchIdKey][$orderKeyId]['profit'] = $profit;
                            $batchInfo[$batchIdKey]['usedAmount'] += $amountProduced;
                            $batchInfo[$batchIdKey]['profit'] += $profit;
                            $sortedOrderBatchOfMinCost = $this->removeOrderFromList($sortedOrderBatchOfMinCost, $orderKeyId);
                        }
                    }

                }
            }
        }
        $this->setBatchInfo($batchInfo);
        $this->setRemainingAmount($remainingAmount);
        $this->setRemainingList($sortedOrderBatchOfMinCost);
        return $toOrderBatch;
    }




    /**
     * 4) Handle allocating the remaining amount, if possible
     * @return array
     * @throws \LogicException
     */
    private function handleRemainingItems(): array
    {
        $array =[];
        $em = $this->getDoctrine()->getManager();
        foreach ($em->getRepository(Batch::class)->findAll() as $batch){
            $array[$batch->getId()] = $this->returnFistCheapCost($batch);
        }
        return $array;
    }

    /**
     * 4) b) finds the
     * @param $batch Batch
     * @return array|null
     * @throws \LogicException
     */
    private function returnFistCheapCost($batch): ?array
    {
        $em = $this->getDoctrine()->getManager();
        $remainingList = $this->getRemainingList();
        $remainingAmount = $this->getRemainingAmount();
        foreach ($remainingList as $orderKey => $batches) {
            /** @var array $batches */
            foreach ($batches as $batchKeyId => $cost) {
                if ($batchKeyId === $batch->getId() && array_key_exists($batch->getId(), $remainingAmount)) {
                    $orderedProductCategry = $em->getRepository(OrderedProductCategory::class)->find($orderKey);
                    if ($orderedProductCategry !== null && !(bool)$orderedProductCategry->getRelatedOrder()->getIsRejected()) {
                        /** @var Order $order */
                        $order = $orderedProductCategry->getRelatedOrder();
                        $producedAmount = $remainingAmount[$batch->getId()];
                        /** @var Product $product */
                        $product = $batch->getRelatedProduct();
                        $halfLife = $product->getHalfLife();
                        $batchEndTime = $batch->getDailyEndTime();
                        $batchUnitCost = $batch->getBatchCost();
                        $targetTime = $order->getTargetTime();
                        $newOrderAmount = $this->getOriginalAmountFromProducedAmount($producedAmount, $halfLife, $targetTime, $batchEndTime);
                        $customer = $order->getRelatedCustomer();
                        $producer = $batch->getRelatedProducer();
                        $transportationCost = $em->getRepository(CarrierCost::class)->findOneBy(array('relatedCustomer' => $customer, 'relatedProducer' => $producer))->getCost();
                        $unitSellPrice = $product->getProductCategory()->getSellPrice();
                        $grantedDiscount = $order->getGrantedDiscount();
                        $profit = $this->getProfit($transportationCost, $batchUnitCost, $newOrderAmount, $halfLife, $batchEndTime, $targetTime, $unitSellPrice, $grantedDiscount);
                        if ($newOrderAmount >= 2000) {
                            $remainingAmount[$batch->getId()] -= $producedAmount;
                            $this->setRemainingAmount($remainingAmount);
                            $remainingList = $this->removeOrderFromList($remainingList, $orderKey);
                            $this->setRemainingList($remainingList);
                            return array($orderKey => array(
                                'amountToOrder' => $newOrderAmount, 'amountToProduce' => $producedAmount, 'profit' => $profit));
                        }
                    }
                }
            }
        }
        return null;
    }

    /**
     *  for   originalComboAlgorithm()
     *  2) Function to count the amount of orders possible for each batch
     * @param $orderBatch array
     * @return array
     */
    private function getHighestOrderBatchCount($orderBatch):array
    {
        $count=[];
        foreach ($orderBatch as $batches) {
            /** @var array $batches */
            foreach ($batches as $batchIdKey => $cost) {
                if (!isset($count[$batchIdKey])){
                    $count[$batchIdKey] = 0;
                }else {
                    ++$count[$batchIdKey];
                }
            }
        }
        arsort($count);
        return $count;
    }

    /**
     *  for   originalComboAlgorithm()
     *  2) Function to count the amount of orders possible for each batch
     * @param $orderBatch array
     * @return array
     */
    private function getLowestOrderBatchCount($orderBatch):array
    {
        $count=[];
        foreach ($orderBatch as $batches) {
            /** @var array $batches */
            foreach ($batches as $batchIdKey => $cost) {
                if (!isset($count[$batchIdKey])){
                    $count[$batchIdKey] = 0;
                }else {
                    ++$count[$batchIdKey];
                }
            }
        }
        asort($count);
        return $count;
    }

    /**
     *  for comboAlgorithm()
     * 3) function to find the least cost order and put it in the Highest batch count first
     *
     * @param $orderBatch array
     * @param bool $batchH
     * @param bool $costH
     * @return array|null
     */
    private function getOptimalOrderList($orderBatch, bool $batchH, bool $costH): ?array
    {
        $toOrderBatch = [];
        $batchInfo =[];
        $remainingAmount =[];
        $batchCapacity =[];
        $em =$this->getDoctrine()->getManager();

        if ($batchH){
            $sortedBatchCount = $this->getHighestOrderBatchCount($orderBatch);
        }else{
            $sortedBatchCount = $this->getLowestOrderBatchCount($orderBatch);
        }

        foreach ($em->getRepository(Batch::class)->findAll() as $batch1){
            $toOrderBatch[$batch1->getId()] =[];
            $batchCapacity[$batch1->getId()] = $batch1->getBatchAmount();
            $batchInfo[$batch1->getId()] = [];
            $batchInfo[$batch1->getId()]['batchCapacity'] =  $batch1->getBatchAmount();
            $batchInfo[$batch1->getId()]['profit'] = 0;
            $batchInfo[$batch1->getId()]['usedAmount'] = 0;

        }

        //prioritizing the isFixed first
        foreach ($sortedBatchCount as $batchIdKey => $value){
            $batch = $em->getRepository(Batch::class)->find($batchIdKey);
            if ($batch !== null) {
                if ($costH){
                    $sortedOrderListForBatch = $this->getHighestOrdersCostForBatch($orderBatch,$batchIdKey);
                } else {
                    $sortedOrderListForBatch = $this->getLowestOrdersCostForBatch($orderBatch,$batchIdKey);
                }
                $batchEndTime = $batch->getDailyEndTime();
                /** @var Product $product */
                $product = $batch->getRelatedProduct();
                $halfLife = $product->getHalfLife();
                $batchUnitCost = $batch->getBatchCost();
                $unitSellPrice =  $product->getProductCategory()->getSellPrice();
                foreach ($sortedOrderListForBatch as $orderIdKey => $cost) {
                    if ($orderIdKey !== null) {
                        $orderedProductCategory = $em->getRepository(OrderedProductCategory::class)->find($orderIdKey);
                        if ($orderedProductCategory && (bool)$orderedProductCategory->getRelatedOrder()->getIsFixed() && !$orderedProductCategory->getRelatedOrder()->getIsRejected()){
                            /** @var Order $order */
                            $order = $orderedProductCategory->getRelatedOrder();
                            $amountOrdered = $orderedProductCategory->getOrderedAmount();
                            $targetTime = $order->getTargetTime();
                            $amountToBeProduced = $this->getAmountToBeProduced($amountOrdered, $halfLife, $targetTime, $batchEndTime);
                            if ($amountToBeProduced <= $batchCapacity[$batchIdKey]) {
                                $grantedDiscount = $order->getGrantedDiscount();
                                $customer = $order->getRelatedCustomer();
                                $producer = $batch->getRelatedProducer();
                                $transportationCost = $em->getRepository(CarrierCost::class)->findOneBy(array('relatedCustomer' => $customer, 'relatedProducer' => $producer))->getCost();
                                $batchCapacity[$batchIdKey] -= $amountToBeProduced;
                                $remainingAmount[$batchIdKey] = $batchCapacity[$batchIdKey];
                                $profit = $this->getProfit($transportationCost, $batchUnitCost, $amountOrdered, $halfLife, $batchEndTime, $targetTime, $unitSellPrice,$grantedDiscount);
                                $toOrderBatch[$batchIdKey][$orderIdKey]['amountToProduce'] = $amountToBeProduced;
                                $toOrderBatch[$batchIdKey][$orderIdKey]['amountOrdered'] = $amountOrdered;
                                $toOrderBatch[$batchIdKey][$orderIdKey]['profit'] = $profit;
                                $batchInfo[$batchIdKey]['usedAmount'] += $amountToBeProduced;
                                $batchInfo[$batchIdKey]['profit'] += $profit;
                                $orderBatch = $this->removeOrderFromList($orderBatch, $orderIdKey);
                            }
                        }
                    }
                }
            }
        }
        foreach ($sortedBatchCount as $batchIdKey => $value){
            $batch = $em->getRepository(Batch::class)->find($batchIdKey);
            if ($batch !== null) {
                if ($costH){
                    $sortedOrderListForBatch = $this->getHighestOrdersCostForBatch($orderBatch,$batchIdKey);
                } else {
                    $sortedOrderListForBatch = $this->getLowestOrdersCostForBatch($orderBatch,$batchIdKey);
                }
                $batchEndTime = $batch->getDailyEndTime();
                /** @var Product $product */
                $product = $batch->getRelatedProduct();
                $halfLife = $product->getHalfLife();
                $batchUnitCost = $batch->getBatchCost();
                $unitSellPrice =  $product->getProductCategory()->getSellPrice();
                foreach ($sortedOrderListForBatch as $orderIdKey => $cost) {
                    if ($orderIdKey !== null) {
                        $orderedProductCategory = $em->getRepository(OrderedProductCategory::class)->find($orderIdKey);
                        if ($orderedProductCategory && !$orderedProductCategory->getRelatedOrder()->getIsRejected()){
                            /** @var Order $order */
                            $order = $orderedProductCategory->getRelatedOrder();
                            $amountOrdered = $orderedProductCategory->getOrderedAmount();
                            $targetTime = $order->getTargetTime();
                            $amountToBeProduced = $this->getAmountToBeProduced($amountOrdered, $halfLife, $targetTime, $batchEndTime);
                            if ($amountToBeProduced <= $batchCapacity[$batchIdKey]) {
                                $grantedDiscount = $order->getGrantedDiscount();
                                $customer = $order->getRelatedCustomer();
                                $producer = $batch->getRelatedProducer();
                                $transportationCost = $em->getRepository(CarrierCost::class)->findOneBy(array('relatedCustomer' => $customer, 'relatedProducer' => $producer))->getCost();
                                $batchCapacity[$batchIdKey] -= $amountToBeProduced;
                                $remainingAmount[$batchIdKey] = $batchCapacity[$batchIdKey];
                                $profit = $this->getProfit($transportationCost, $batchUnitCost, $amountOrdered, $halfLife, $batchEndTime, $targetTime, $unitSellPrice,$grantedDiscount);
                                $toOrderBatch[$batchIdKey][$orderIdKey]['amountToProduce'] = $amountToBeProduced;
                                $toOrderBatch[$batchIdKey][$orderIdKey]['amountOrdered'] = $amountOrdered;
                                $toOrderBatch[$batchIdKey][$orderIdKey]['profit'] = $profit;
                                $batchInfo[$batchIdKey]['usedAmount'] += $amountToBeProduced;
                                $batchInfo[$batchIdKey]['profit'] += $profit;
                                $orderBatch = $this->removeOrderFromList($orderBatch, $orderIdKey);
                            }
                        }
                    }
                }
            }
        }
        $this->setBatchInfo($batchInfo);
        $this->setRemainingAmount($remainingAmount);
        $this->setRemainingList($orderBatch);
        return $toOrderBatch;
    }



    /*********************************************************************************************************
     * Composite Function that can Be called From the Super_Admin_Controller
     * iterate starting from minimal order cost upwards
     ****************************************************************************************************/

    /**
     *
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     * @throws \LogicException
     */
    protected function originalAlgorithm(): void
    {
        $em = $this->getDoctrine()->getManager();
        $orderBatch =$this->getListOfOrderBatches();
        $orderBatchOfMinCost = $this->chooseMinimumCost($orderBatch);
        $sortedOrderBatchOfMinCost= $this->sortByMinCost($orderBatchOfMinCost);
        $optimizedOrderList = $this->getOptimizedOrderList($sortedOrderBatchOfMinCost);
        $remainingList = $this->handleRemainingItems();
        $batchInfo = $this->getBatchInfo();
        foreach ($remainingList as $batchIdKey => $orders){
            if ($orders !== null){
                /** @var array $orders */
                foreach ($orders as $orderIdKey => $newAmount){
                    $optimizedOrderList[$batchIdKey][$orderIdKey]['amountToProduce'] = $newAmount['amountToProduce'];
                    $optimizedOrderList[$batchIdKey][$orderIdKey]['amountOrdered'] =$newAmount['amountToOrder'];
                    $optimizedOrderList[$batchIdKey][$orderIdKey]['profit'] =$newAmount['profit'];
                    $batchInfo[$batchIdKey]['usedAmount'] += $newAmount['amountToProduce'];
                    $batchInfo[$batchIdKey]['profit'] += $newAmount['profit'];
                }
            }
            // calculate new loss
            $batchLoss = $batchInfo[$batchIdKey]['batchCapacity'] - $batchInfo[$batchIdKey]['usedAmount'];
            $batch = $em->getRepository(Batch::class)->find($batchIdKey);
            if ($batch !== null){
                $batchLoss *= $batch->getBatchCost();
            }
            $batchInfo[$batchIdKey]['profit'] -= $batchLoss;
        }
        $this->setBatchInfo($batchInfo);
        $this->setOptimalListToOrder($optimizedOrderList);
    }


    /*********************************************************************************************************
     * Composite Function that can Be called From the Super_Admin_Controller
     * Handles between combination of two factors (orders to batch Count and Cost of order)
     * and if High or low for both
     ****************************************************************************************************/

    /**
     * @param bool $batchH
     * @param bool $costH
     * @return void
     */
    protected function comboAlgorithm(bool $batchH,bool $costH):void
    {
        $em = $this->getDoctrine()->getManager();
        $orderBatch =$this->getListOfOrderBatches();
        $optimizedList =$this->getOptimalOrderList($orderBatch, $batchH, $costH);
        $remainingList =$this->handleRemainingItems();
        $batchInfo = $this->getBatchInfo();
        foreach ($remainingList as $batchIdKey => $orders){
            if ($orders !== null){
                /** @var array $orders */
                foreach ($orders as $orderIdKey => $newAmount){
                    $optimizedList[$batchIdKey][$orderIdKey]['amountToProduce'] =$newAmount['amountToProduce'];
                    $optimizedList[$batchIdKey][$orderIdKey]['amountOrdered'] =$newAmount['amountToOrder'];
                    $optimizedList[$batchIdKey][$orderIdKey]['profit'] =$newAmount['profit'];
                    $batchInfo[$batchIdKey]['usedAmount'] += $newAmount['amountToProduce'];
                    $batchInfo[$batchIdKey]['profit'] += $newAmount['profit'];
                }
            }
            $batchLoss = $batchInfo[$batchIdKey]['batchCapacity'] - $batchInfo[$batchIdKey]['usedAmount'];
            $batch = $em->getRepository(Batch::class)->find($batchIdKey);
            if ($batch !== null){
                $batchLoss *= $batch->getBatchCost();
            }
            $batchInfo[$batchIdKey]['profit'] -= $batchLoss;
        }
        $this->setBatchInfo($batchInfo);
        $this->setOptimalListToOrder($optimizedList);
    }



    /*********************************************************************************************************
     * Composite Function that can Be called From the Super_Admin_Controller
     * minimal order cost starting fom highest cost
     ****************************************************************************************************/

    /**
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     * @throws \LogicException
     */
    protected function originalReverseAlgorithm(): void
    {
        $em = $this->getDoctrine()->getManager();
        $orderBatch =$this->getListOfOrderBatches();
        $orderBatchOfMinCost = $this->chooseMinimumCost($orderBatch);
        $sortedOrderBatchOfMinCost= $this->sortByMaxCost($orderBatchOfMinCost);
        $optimizedOrderList = $this->getOptimizedOrderList($sortedOrderBatchOfMinCost);
        $remainingList = $this->handleRemainingItems();
        $batchInfo = $this->getBatchInfo();
        foreach ($remainingList as $batchIdKey => $orders){
            if ($orders !== null){
                /** @var array $orders */
                foreach ($orders as $orderIdKey => $newAmount){
                    $optimizedOrderList[$batchIdKey][$orderIdKey]['amountToProduce'] = $newAmount['amountToProduce'];
                    $optimizedOrderList[$batchIdKey][$orderIdKey]['amountOrdered'] =$newAmount['amountToOrder'];
                    $optimizedOrderList[$batchIdKey][$orderIdKey]['profit'] =$newAmount['profit'];
                    $batchInfo[$batchIdKey]['usedAmount'] += $newAmount['amountToProduce'];
                    $batchInfo[$batchIdKey]['profit'] += $newAmount['profit'];
                }
            }
            // calculate new loss
            $batchLoss = $batchInfo[$batchIdKey]['batchCapacity'] - $batchInfo[$batchIdKey]['usedAmount'];
            $batch = $em->getRepository(Batch::class)->find($batchIdKey);
            if ($batch !== null){
                $batchLoss *= $batch->getBatchCost();
            }
            $batchInfo[$batchIdKey]['profit'] -= $batchLoss;
        }
        $this->setBatchInfo($batchInfo);
        $this->setOptimalListToOrder($optimizedOrderList);
    }
}