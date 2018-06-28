<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 25.10.2017
 * Time: 11:09
 */

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="carrier")
 */
class Carrier
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
    private $costPerKm;
    
    
    /**
     * @ORM\OneToOne(targetEntity="Account", inversedBy="relatedCarrierEntry")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private $relatedAccount;

    /**
     * @ORM\OneToMany(targetEntity="Producer", mappedBy="relatedCarrier", fetch="EAGER")
     */
    private $relatedProducers;


    /**
     * @ORM\OneToMany(targetEntity="CarrierCost", mappedBy="relatedCarrier", cascade={"remove"})
     *
     */
    private $relatedCarrierCost;

    /**
     * @ORM\OneToOne(targetEntity="Address", cascade={"persist"})
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private $primaryAddress;

    public function __construct()
    {
        $this->relatedProducers = new ArrayCollection();
        $this->relatedCarrierCost = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getCostPerKm()
    {
        return $this->costPerKm;
    }

    /**
     * @param mixed $costPerKm
     */
    public function setCostPerKm($costPerKm):void
    {
        $this->costPerKm = $costPerKm;
    }

    /**
     * @return Account|null
     */
    public function getRelatedAccount():?Account
    {
        return $this->relatedAccount;
    }

    /**
     * @param Account|null $relatedAccount
     */
    public function setRelatedAccount(Account $relatedAccount=  null):void
    {
        $this->relatedAccount = $relatedAccount;
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

    /**
     * @return ArrayCollection|Producer[]
     */
    public function getRelatedProducers()
    {
        return $this->relatedProducers;
    }

    /**
     * @param Producer $producer
     * @return bool
     */
    public function addRelatedProducer(Producer $producer):bool
    {
        if(!$this->relatedProducers->contains($producer)){
            $this->relatedProducers[] = $producer;
            $producer->setRelatedCarrier($this);
            return true;
        }
        return false;
    }

    /**
     * @param Producer $producer
     * @return bool
     */
    public function removeRelatedProducer(Producer $producer):bool
    {
        if(!$this->relatedProducers->contains($producer)){
            return false;
        }
        $this->relatedProducers->removeElement($producer);
        $producer->setRelatedCarrier(null);
        return true;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getRelatedAccount()->getContactName().'';
    }


    /**
     * @return ArrayCollection|CarrierCost[]
     */
    public function getRelatedCarrierCost()
    {
        return $this->relatedCarrierCost;
    }

    /**
     * @param CarrierCost carrierCost
     * @return bool
     */
    public function addRelatedCarrierCost(CarrierCost $carrierCost):bool
    {
        if (!$this->relatedCarrierCost->contains($carrierCost)) {
            $this->relatedCarrierCost[] = $carrierCost;
            $carrierCost->setRelatedCarrier($this);
            return true;
        }
        return false;
    }

    /**
     * @param CarrierCost carrierCost
     * @return bool
     */
    public function removeRelatedCarrierCost(CarrierCost $carrierCost):bool
    {
        if (!$this->relatedCarrierCost->contains($carrierCost)) {
            return false;
        }
        $this->relatedCarrierCost->removeElement($carrierCost);
        $carrierCost->setRelatedCarrier(null);

        return true;
    }

    /**
     * @return Address|null
     */
    public function getPrimaryAddress(): ?Address
    {
        return $this->primaryAddress;
    }

    /**
     * @param Address|null $primaryAddress
     */
    public function setPrimaryAddress(Address $primaryAddress=null):void
    {
        $this->primaryAddress = $primaryAddress;
    }

}