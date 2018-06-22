<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 25.10.2017
 * Time: 11:35
 */
namespace app\Entity;


use AppBundle\Entity\Interfaces\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="customer")
 * @ORM\HasLifecycleCallbacks()
 */
class Customer implements UserInterface
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $institution;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $customerNumber;

    /**
     * @ORM\OneToOne(targetEntity="Account", inversedBy="relatedCustomerEntry", fetch="EAGER")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     */
    private $relatedAccount;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Iban(payload = {"severity" = "error"})
     */
    private $debitNumber;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $toPayDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isUgValid;

    /**
     * @ORM\OneToOne(targetEntity="Document", cascade={"persist", "remove"}, fetch="EAGER")
     * @ORM\JoinColumn(name="related_document_for_ug", referencedColumnName="id", nullable=false)
     */
    private $relatedUg;

    /**
     * @ORM\ManyToOne(targetEntity="DiscountGroup", inversedBy="relatedCustomers", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $discountGroup;

    /**
     * @ORM\OneToMany(targetEntity="Order", mappedBy="relatedCustomer", fetch="EAGER")
     */
    private $relatedOrders;

    /**
     * @ORM\OneToMany(targetEntity="CarrierCost", mappedBy="relatedCustomer", cascade={"remove"})
     */
    private $relatedCarrierCost;

    /**
     * @ORM\OneToOne(targetEntity="Address", cascade={"persist"})
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     * @Assert\Type(type="AppBundle\Entity\Address")
     * @Assert\Valid()
     */
    private $shippingAddress;

    /**
     * @ORM\OneToOne(targetEntity="Address", cascade={"persist"})
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private $billAddress;



    public function __construct()
    {
        $this->relatedOrders = new ArrayCollection();
        $this->relatedCarrierCost = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getInstitution(): string
    {
        return $this->institution.'';
    }

    /**
     * @param string $institution
     */
    public function setInstitution(string $institution= null): void
    {
        $this->institution = $institution.'';
    }

    /**
     * @return string|null
     */
    public function getCustomerNumber(): string
    {
        return $this->customerNumber.'';
    }

    /**
     * @param string|null $customerNumber
     */
    public function setCustomerNumber(string  $customerNumber= null): void
    {
        $this->customerNumber = $customerNumber.'';
    }

    /**
     * @return Account
     */
    public function getRelatedAccount(): Account
    {
        return $this->relatedAccount;
    }

    /**
     * @param Account $relatedAccount
     */
    public function setRelatedAccount($relatedAccount):void
    {
        $this->relatedAccount = $relatedAccount;
    }

    /**
     * @return string
     */
    public function getDebitNumber(): string
    {
        return $this->debitNumber.'';
    }

    /**
     * @param string $debitNumber
     */
    public function setDebitNumber(string $debitNumber= null): void
    {
        $this->debitNumber = $debitNumber.'';
    }

    /**
     * @return DateTime|null
     */
    public function getToPayDate(): ?DateTime
    {
        return $this->toPayDate;
    }

    /**
     * @param DateTime|null $toPayDate
     */
    public function setToPayDate(DateTime $toPayDate = null): void
    {
        $this->toPayDate = $toPayDate;
    }

    /**
     * @return bool
     */
    public function getIsUgValid(): bool
    {
        return (bool)$this->isUgValid;
    }

    /**
     * @param bool $isUgValid
     */
    public function setIsUgValid(bool $isUgValid = false): void
    {
        $this->isUgValid = $isUgValid;
    }

    /**
     * @return DiscountGroup|null
     */
    public function getDiscountGroup(): ?DiscountGroup
    {
        return $this->discountGroup;
    }

    /**
     * @param DiscountGroup|null $discountGroup
     */
    public function setDiscountGroup(DiscountGroup $discountGroup = null): void
    {
        $this->discountGroup = $discountGroup;
    }

    /**
     * @return Document|null
     */
    public function getRelatedUg(): ?Document
    {
        return $this->relatedUg;
    }

    /**
     * @param Document $relatedUg
     */
    public function setRelatedUg($relatedUg): void
    {
        $this->relatedUg = $relatedUg;
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
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return ArrayCollection|Order[]
     */
    public function getRelatedOrders()
    {
        return $this->relatedOrders;
    }


    /**
     * @param Order $order
     * @return bool
     */
    public function addRelatedOrder(Order $order): ?bool
    {
        if(!$this->relatedOrders->contains($order)){
            $this->relatedOrders[] = $order;
            $order->setRelatedCustomer($this);
            return true;
        }
        return false;
    }

    /**
     * @param Order $order
     * @return bool
     */
    public function removeRelatedOrder(Order $order): ?bool
    {
        if(!$this->relatedOrders->contains($order)){
            return false;
        }
        $this->relatedOrders->removeElement($order);
        $order->setRelatedCustomer(null);
        return true;
    }

    /**
     * @return ArrayCollection|CarrierCost[]
     */
    public function getRelatedCarrierCost()
    {
        return $this->relatedCarrierCost;
    }

    /**
     * @param CarrierCost $carrierCost
     * @return bool
     */
    public function addRelatedCarrierCost(CarrierCost $carrierCost): ?bool
    {
        if (!$this->relatedCarrierCost->contains($carrierCost)) {
            $this->relatedCarrierCost[] = $carrierCost;
            $carrierCost->setRelatedCustomer($this);
            return true;
        }
        return false;
    }

    /**
     * @param CarrierCost $carrierCost
     * @return bool
     */
    public function removeRelatedCarrierCost(CarrierCost $carrierCost): ?bool
    {
        if (!$this->relatedCarrierCost->contains($carrierCost)) {
            return false;
        }
        $this->relatedCarrierCost->removeElement($carrierCost);
        $carrierCost->setRelatedCustomer(null);

        return true;
    }

    public function __toString()
    {
        if ($this->getRelatedAccount()){
            return $this->getRelatedAccount()->getContactName().'';
        }
            return '';


    }

    /**
     * @return Address|null
     */
    public function getShippingAddress(): ?Address
    {
        return $this->shippingAddress;
    }

    /**
     * @param Address $shippingAddress
     */
    public function setShippingAddress(Address $shippingAddress): void
    {
        $this->shippingAddress = $shippingAddress;
        if($shippingAddress->getRelatedAccount() === null){
            $shippingAddress->setRelatedAccount($this->relatedAccount);
        }
    }

    /**
     * @return Address|null
     */
    public function getBillAddress(): ?Address
    {
        return $this->billAddress;
    }

    /**
     * @param Address $billAddress
     */
    public function setBillAddress(Address $billAddress):void
    {
        $this->billAddress = $billAddress;
        if($billAddress->getRelatedAccount() === null){
            $billAddress->setRelatedAccount($this->relatedAccount);
        }
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersist(): void
    {
        if ($this->getShippingAddress()!==null){
            $this->getShippingAddress()->setRelatedAccount($this->relatedAccount);
            if($this->getBillAddress() === null || $this->getBillAddress()->getZip() === ''){
                $this->setBillAddress($this->getShippingAddress());
            }
            $this->getBillAddress()->setRelatedAccount($this->relatedAccount);
        }
    }
}