<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 29/11/2017
 * Time: 14:20
 *
 * https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=Washington,DC&destinations=New+York+City,NY&key=YOUR_API_KEY
 * https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=Washington,DC&destinations=New+York+City,NY&AIzaSyCok4jZhx21rgKeCIhw096dGc0mwe0M6_4
 */

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Carrier;
use App\Entity\CarrierCost;
use App\Entity\Customer;
use App\Entity\Producer;
use Doctrine\Common\Persistence\ObjectManager;
use Ivory\GoogleMap\Service\Base\Distance;
use Ivory\GoogleMap\Service\Base\Duration;
use Ivory\GoogleMap\Service\Base\UnitSystem;
use Ivory\GoogleMap\Service\DistanceMatrix\Request\DistanceMatrixRequest;
use Ivory\GoogleMap\Service\DistanceMatrix\Response\DistanceMatrixResponse;
use Ivory\GoogleMap\Service\Geocoder\Request\GeocoderAddressRequest;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Ivory\GoogleMap\Service\Base\Location\AddressLocation;
use \Psr\SimpleCache\InvalidArgumentException;

class GoogleController extends Controller
{
    /**
     * @var ArrayCache
     */
    protected $cache;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this -> cache = new ArrayCache();
        $this -> logger = $logger;
    }

    /**
     * @return void
     * @throws \RuntimeException
     * @throws \LogicException
     */
    public function iterateAction():void{
        $coordinates = null;
        try{
            $em = $this->getDoctrine()->getManager();
        } catch(\LogicException $e){
            $this -> logger -> emergency('Doctrine in Google Controller failed: '.$e -> getMessage());
            $this->addFlash('error','Doctrine Failed');
            $em = null;
        }
        $this->getAndSetLatAndLong($em);
        $producers = $em->getRepository(Producer::class)->findAll();
        $customers = $em->getRepository(Customer::class)->findAll();
        /** @var Producer $producer */
        foreach ($producers as $producer){
            /** @var Customer $customer */
            foreach ($customers as $customer) {
                $origin=null;
                /** @var Address $originAddress */
                $originAddress = $producer->getPickUpAddress();
                if ($originAddress){
                $origin =
                    $originAddress->getStreet() . ' ' .
                    $originAddress->getBuildingNumber(). ', ' .
                    $originAddress->getZip(). ' ' .
                    $originAddress->getCity(). ', ' .
                    $originAddress->getCountry();

                } else{
                    $this->logger->info('Google Producer Address Error');
                    $this->logger->error('Producer PickUp Address is empty');
                }
                /** @var Address $destinationAddress */
                $destinationAddress = $customer->getShippingAddress();
                $destination =
                    $destinationAddress->getStreet() . ' ' .
                    $destinationAddress->getBuildingNumber(). ', ' .
                    $destinationAddress->getZip(). ' ' .
                    $destinationAddress->getCity(). ', ' .
                    $destinationAddress->getCountry();
                /** @var Carrier $carrier */
                $carrier = $producer->getRelatedCarrier();

                //create a new CarrierCost Entity
                try{
                    $entityExists = $this->checkCarrierCostEntity($customer,$carrier,$producer);
                } catch(\LogicException $e){
                    $this->logger->info('Google Address Error: ' . $e->getTraceAsString());
                    $this->logger->error('checkCarrierCostEntity is empty ' . $e->getMessage());
                    $this->addFlash('error','checkCarrierCostEntity Can not find if entity carrierCost exist');
                    continue;
                }
                $distance = null;
                $duration = null;
                $timeInMinutes = null;
                $cost =null;

                try{
                    $distance = $this -> getDistanceForJourneyBetween($origin, $destination);
                    $duration = $this -> getEstimatedDurationForJourneyBetween($origin, $destination);
                } catch (\LogicException $e ){
                    $this -> logger ->critical('Google Api failed: '.$e -> getMessage());
                    $this->addFlash('error','Duration and Distance cant be found for ' . $customer);

                    //TODO: submit message or error saying the value apicall failed

                    // skip customer, because api call failed
                    continue;
                }
                //round up the time in minutes
                $timeInMinutes = ceil($duration -> getValue()/60);
                //change the distance from meters to kilometers
                $distance = ceil($distance->getValue()/1000);
                //calculate the cost of delivery to add it to the database CarrierCost
                $cost = $carrier->getCostPerKm() * $distance;
                if ($entityExists === false) {
                    $carrierCost = new CarrierCost();
                    $carrierCost->setRelatedCustomer($customer);
                    $carrierCost->setRelatedCarrier($carrier);
                    $carrierCost->setRelatedProducer($producer);
                    $carrierCost->setDistance($distance);
                    $carrierCost->setEstimatedTime($timeInMinutes);
                    $carrierCost->setCost($cost); //initial cost, must be updated later
                    $em->persist($carrierCost);
                } else{
                    $entityExists->setDistance($distance);
                    $entityExists->setEstimatedTime($timeInMinutes);
                    $entityExists->setCost($cost);
                    $em->persist($entityExists);
                }
            }
        }
        $em->flush();
    }

    /**
     * @param Customer $customer
     * @param Carrier $carrier
     * @param Producer $producer
     * Function to check if the Entity Exists
     * @return CarrierCost|Customer|Producer|bool
     * @throws \LogicException
     */
    private function checkCarrierCostEntity(Customer $customer, Carrier $carrier, Producer $producer){
        $em = $this->getDoctrine()->getManager();
        foreach ($em->getRepository(CarrierCost::class)->findAll() as $carrierCost){
            $carrierTest = $carrierCost->getRelatedCarrier();
            $producerTest = $carrierCost->getRelatedProducer();
            $customerTest = $carrierCost->getRelatedCustomer();
            if ($carrier === $carrierTest && $producer === $producerTest && $customer === $customerTest)
            {return $carrierCost;}
        }
        return false;
    }

    /**
     * @TODO move this to a service
     * @param $origin
     * @param $destination
     * @param null $arrivalTime
     * @param null $departureTime
     * @return null|Distance
     * @throws \RuntimeException
     * @throws \LogicException
     */
    private function getDistanceForJourneyBetween($origin, $destination, $arrivalTime = null, $departureTime = null):?Distance
    {
        $returnValue = null;
        $response = $this -> getGoogleMapsInformationForJourneyBetween($origin, $destination, $arrivalTime, $departureTime);
        foreach ($response->getRows() as $row) {
            foreach ($row->getElements() as $element) {
                if(!$element -> hasDistance()){
                    $this -> logger ->critical('Google Api failed at get distance');
                    continue;
                }
                $returnValue = $element -> getDistance();
            }
        }
        return $returnValue;
    }

    /**
     * @TODO move this to a service
     * @param $origin
     * @param $destination
     * @param null $arrivalTime
     * @param null $departureTime
     * @return Duration
     * @throws \RuntimeException
     * @throws \LogicException
     */
   private function getEstimatedDurationForJourneyBetween($origin, $destination, $arrivalTime = null, $departureTime = null):Duration
    {
        $returnValue = null;
        $response = $this -> getGoogleMapsInformationForJourneyBetween($origin, $destination, $arrivalTime, $departureTime);
        foreach ($response->getRows() as $row) {
            foreach ($row->getElements() as $element) {
                if(!$element -> hasDuration()){
                    $this -> logger ->critical('Google Api failed at get distance');
                    continue;
                }
                $returnValue = $element -> getDuration();
            }
        }
        return $returnValue;
    }

    /**
     *  @TODO move this to a service
     * TODO: handle correcting origin and destination, or just use coordinate system
     * @param $origin
     * @param $destination
     * @param null $arrivalTime
     * @param null $departureTime
     * @return DistanceMatrixResponse
     * @throws \LogicException
     */
    private function getGoogleMapsInformationForJourneyBetween($origin, $destination, $arrivalTime = null, $departureTime = null):DistanceMatrixResponse
    {
        $identifier = hash('sha256',$origin.$destination.$arrivalTime.$departureTime);
        $departureTime = new \DateTime('tomorrow 3am');
        if(!$this -> cache -> has($identifier)){
            $request = new DistanceMatrixRequest(
                [new AddressLocation($origin)],
                [new AddressLocation($destination)]
            );
            $request->setArrivalTime($arrivalTime);
            $request->setDepartureTime($departureTime);
            $request->setUnitSystem(UnitSystem::METRIC);

            try{
                $response = $this->container->get('ivory.google_map.distance_matrix')->process($request);
                $this -> cache -> set($identifier,$response);
            } catch (ServiceNotFoundException $e){
                throw new \LogicException($e -> getMessage());
            } catch (ServiceCircularReferenceException $e){
                throw new \LogicException($e -> getMessage());
            } catch (InvalidArgumentException $e){
                throw new \LogicException($e -> getMessage());
            }
        }
        try{
            return $this -> cache -> get($identifier);
        } catch (InvalidArgumentException $e){
            throw new \LogicException($e -> getMessage());
        }
    }

    /**
     * @param ObjectManager $em
     */
    private function getAndSetLatAndLong(ObjectManager $em): void
    {
        foreach ($em->getRepository(Address::class)->findAll() as $address){
            $coordinates = null;
            $toleranceValue = 0;
            $requestAddress =
                $address->getStreet() . ' ' .
                $address->getBuildingNumber(). ', ' .
                $address->getZip(). ' ' .
                $address->getCity(). ', ' .
                $address->getCountry();
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
                $em->persist($address);
            }
        }
        $em->flush();
    }
}