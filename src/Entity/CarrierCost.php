<?php

namespace app\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="carrier_cost")
 */
class CarrierCost
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="decimal", precision=20, scale=4)
     */
    private $cost;

    /**
     * Ceil kilometers!
     * @ORM\Column(type="integer")
     */
    private $distance;


    /**
     * in minutes integer
     * @ORM\Column(type="integer")
     */
    private $estimatedTime;

    /**
     * @ORM\ManyToOne(targetEntity="Carrier", inversedBy="relatedCarrierCost")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private $relatedCarrier;

    /**
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="relatedCarrierCost")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private $relatedCustomer;

    /**
     * @ORM\ManyToOne(targetEntity="Producer", inversedBy="relatedCarrierCost")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private $relatedProducer;

    public function __toString()
    {
        return 'Carrier Cost #' . $this->id;
    }

    /**
     * @return mixed
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param mixed $cost
     */
    public function setCost($cost):void
    {
        $this->cost = $cost;
    }

    /**
     * @return Carrier|null
     */
    public function getRelatedCarrier(): ?Carrier
    {
        return $this->relatedCarrier;
    }

    /**
     * @param Carrier|null $relatedCarrier
     */
    public function setRelatedCarrier(Carrier $relatedCarrier = null):void
    {
        $this->relatedCarrier = $relatedCarrier;
    }

    /**
     * @return Customer|null
     */
    public function getRelatedCustomer(): ?Customer
    {
        return $this->relatedCustomer;
    }

    /**
     * @param Customer|null $relatedCustomer
     */
    public function setRelatedCustomer(Customer $relatedCustomer=null):void
    {
        $this->relatedCustomer = $relatedCustomer;
    }

    /**
     * @return Producer|null
     */
    public function getRelatedProducer():?Producer
    {
        return $this->relatedProducer;
    }

    /**
     * @param Producer|null $relatedProducer
     */
    public function setRelatedProducer(Producer $relatedProducer=null):void
    {
        $this->relatedProducer = $relatedProducer;
    }

    /**
     * @return int
     */
    public function getDistance(): int
    {
        return (int)$this->distance;
    }

    /**
     * @param int $distance
     */
    public function setDistance(int $distance = 0): void
    {
        $this->distance = $distance;
    }

    /**
     * @return int
     */
    public function getEstimatedTime():int
    {
        return (int)$this->estimatedTime;
    }

    /**
     * @param integer|null $estimatedTime
     */
    public function setEstimatedTime(int $estimatedTime = 0):void
    {
        $this->estimatedTime = $estimatedTime;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id):void
    {
        $this->id = $id;
    }

}