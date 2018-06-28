<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 22.11.2017
 * Time: 11:10
 */

namespace App\Twig;

use App\Entity\Account;
use App\Entity\Address;
use ReflectionClass;
use ReflectionException;
use Twig_Filter as Filter;

class Filters extends \Twig_Extension
{

    public function getFilters():array
    {
        // if we stick to twig version 2
        return array(
            new Filter('is_class', array($this, 'isClass')),
            new Filter('guess_address_type' , array($this, 'guessAddressType'))
        );
    }

    public function isClass($object , $checkForType): bool
    {
        $reflect = null;
        if(!\is_object($object)){
            return false;
        }
        try {
            $reflect = new ReflectionClass($object);
        } catch (ReflectionException $e) {
        }
        return $reflect->getShortName() === $checkForType;
    }

    /**
     * @param Address $object
     * @return string
     */
    public function guessAddressType($object): ?string
    {
        if(!$this->isClass($object, 'Address')){
            return '';
        }

        // Check objects
        /** @var Account $account */
        $account = $object->getRelatedAccount();
        $result = '';
        if ($account) {
            if($account->getRelatedCustomerEntry() !== null && $account->getRelatedCustomerEntry()->getShippingAddress() === $object){
                $result.='Shipping address / ';
            }
            if($account->getRelatedCustomerEntry() !== null && $account->getRelatedCustomerEntry()->getBillAddress() === $object){
                $result.='Billing address / ';
            }
            if($account->getRelatedProducerEntry() !== null && $account->getRelatedProducerEntry()->getPickUpAddress() === $object){
                $result.='Pickup address / ';
            }
        }
        $result = substr($result, 0, -2);
        return $result;
    }
}