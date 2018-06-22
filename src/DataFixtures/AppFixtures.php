<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 05/06/2018
 * Time: 10:31
 */

namespace src\DataFixtures;

use AppBundle\Entity\Customer;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderedProductCategory;
use AppBundle\Entity\ProductCategory;
use AppBundle\Entity\ProductSellSizes;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $productCategory = $manager->getRepository(ProductCategory::class)->find(1);
        $customers = $manager->getRepository(Customer::class)->findAll();
        for ($i = 0; $i < 3; $i++){
            foreach ($customers as $customer){
                $order = new Order();
                $order->setFlag('new');
                $order->setComment(null);
                $order->setIsOptimized(false);
                $order->setRelatedCustomer($customer);
                $order->setGrantedDiscount(random_int(0,10)/100);
                $order->setIsFixed(false);
                $order->setIsRejected(false);
                if($i === 0){
                    $order->setTargetTime(new \DateTime('tomorrow 8 am'));
                } else {
                    $order->setTargetTime(new \DateTime('tomorrow 8 am + '.$i.'day'));
                }
                $order->setBill(null);
                $order->setOrderConformation(null);
                $order->setDateOrdered(new \DateTime('now'));
                $order->setCustomerBillingAddress($customer->getBillAddress());
                $order->setCustomerShippingAddress($customer->getShippingAddress());
                $manager->persist($order);
                $orderedProductCategory = new OrderedProductCategory();
                $orderedProductCategory->setRelatedOrder($order);
                $orderedProductCategory->setRelatedProductCategory($productCategory);
                $productSellSizes = $manager->getRepository(ProductSellSizes::class)->find(random_int(1,8));
                $orderedProductCategory->setRelatedBulkDiscount($productSellSizes->getDiscountInPercent()/100);
                $orderedProductCategory->setRelatedBuyPrice($productCategory->getSellPrice()*$productSellSizes->getAmount()*(1-($productSellSizes->getDiscountInPercent()/100)));
                $orderedProductCategory->setOrderedAmount($productSellSizes->getAmount());
                $orderedProductCategory->setDeliveredAmount(0);
                $orderedProductCategory->setADRDocument(null);
                $orderedProductCategory->setRelatedOrderedProduct(null);
                $manager->persist($orderedProductCategory);
            }
            $manager->flush();
        }
    }
}