<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 25.10.2017
 * Time: 11:10
 */

namespace App\Entity;


use App\Entity\Interfaces\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="producer")
 * @ORM\HasLifecycleCallbacks()
 */
class Producer implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    private $number;

    /**
     * @ORM\Column(type="string", nullable=true, columnDefinition="ENUM( 'Dr.', 'Prof.', 'Prof. Dr.', 'Herr', 'Frau' )")
     */
    private $headTitle;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $headFirstName;


    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $headLastName;

    /**
     * @ORM\ManyToOne(targetEntity="Carrier", inversedBy="relatedProducers", cascade={"persist"}, fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private $relatedCarrier;


    /**
     * @ORM\OneToOne(targetEntity="Account", inversedBy="relatedProducerEntry", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private $relatedAccount;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Batch", mappedBy="relatedProducer")
     */
    private $relatedBatches;

    /**
     * @ORM\OneToOne(targetEntity="Address", cascade={"persist"})
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private $pickUpAddress;

    /**
     * @ORM\OneToMany(targetEntity="CarrierCost", mappedBy="relatedProducer", cascade={"remove"})
     */
    private $relatedCarrierCost;

    public function __construct()
    {
        $this->relatedBatches = new ArrayCollection();
        $this->relatedCarrierCost = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number.'';
    }

    /**
     * @param string|null $number
     */
    public function setNumber(string $number = null):void
    {
        $this->number = $number.'';
    }

    /**
     * @return string
     */
    public function getHeadFirstName(): string
    {
        return $this->headFirstName.'';
    }

    /**
     * @param string|null $headFirstName
     */
    public function setHeadFirstName(string $headFirstName = null):void
    {
        $this->headFirstName = $headFirstName.'';
    }

    /**
     * @return string|null
     */
    public function getHeadTitle(): string
    {
        return $this->headTitle.'';
    }

    /**
     * @param string|null $headTitle
     */
    public function setHeadTitle(string $headTitle=null):void
    {
        $this->headTitle = $headTitle.'';
    }

    /**
     * @return string
     */
    public function getHeadLastName(): string
    {
        return $this->headLastName.'';
    }

    /**
     * @param string|null $headLastName
     */
    public function setHeadLastName(string $headLastName=null):void
    {
        $this->headLastName = $headLastName.'';
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
    public function setRelatedCarrier(Carrier $relatedCarrier = null): void
    {
        $this->relatedCarrier = $relatedCarrier;
    }

    /**
     * @return Account
     */
    public function getRelatedAccount(): ?Account
    {
        return $this->relatedAccount;
    }

    /**
     * @param Account $relatedAccount
     */
    public function setRelatedAccount($relatedAccount): void
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
     * @return ArrayCollection|Batch[]
     */
    public function getRelatedBatches()
    {
        return $this->relatedBatches;
    }

    /**
     * @param Batch $batch
     * @return bool
     */
    public function addRelatedBatch(Batch $batch): bool
    {
        if(!$this->relatedBatches->contains($batch)){
            $this->relatedBatches[] = $batch;
            $batch->setRelatedProducer($this);
            return true;
        }
        return false;
    }

    /**
     * @param Batch $batch
     * @return bool
     */
    public function removeRelatedCustomer(Batch $batch): bool
    {
        if(!$this->relatedBatches->contains($batch)){
            return false;
        }
        $this->relatedBatches->removeElement($batch);
        $batch->setRelatedProducer(null);
        return true;
    }

    /**
     * @return Address
     */
    public function getPickUpAddress(): ?Address
    {
        return $this->pickUpAddress;
    }

    /**
     * @param Address $pickUpAddress
     */
    public function setPickUpAddress(Address $pickUpAddress):void
    {
        $this->pickUpAddress = $pickUpAddress;
        if($pickUpAddress->getRelatedAccount() === null){
            $pickUpAddress->setRelatedAccount($this->relatedAccount);
        }
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
            $carrierCost->setRelatedProducer($this);
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
        $carrierCost->setRelatedProducer(null);

        return true;
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersist():void
    {
        if ($this->getPickUpAddress() !== null) {
        $this->getPickUpAddress()->setRelatedAccount($this->relatedAccount);
        }
    }

    public function __toString()
    {
        return $this->getRelatedAccount()->getContactName().'';
    }

}