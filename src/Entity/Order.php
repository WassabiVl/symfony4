<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 25.10.2017
 * Time: 13:27
 */
namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="orders")
 */
class Order
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;
    /**
     * Flags created automatically depending on how the order is altered
     * @ORM\Column(type="string", nullable=false)
     */
    private $flag;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;
    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isOptimized;
    /**
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="relatedOrders", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private $relatedCustomer;
    /**
     * @ORM\Column(type="decimal", precision=20, scale=4, nullable=true)
     */
    private $grantedDiscount;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isFixed;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isRejected;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $targetTime;

    /**
     * @ORM\OneToOne(targetEntity="Document",cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="bill_related_document", referencedColumnName="id", nullable=true)
     */
    private $bill;

    /**
     * @ORM\OneToOne(targetEntity="Document", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="order_conformation_related_document", referencedColumnName="id", nullable=true)
     */
    private $orderConformation;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="create")
     */
    private $dateOrdered;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\OrderedProductCategory", mappedBy="relatedOrder")
     */
    private $relatedOrderedCategorys;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $customerShippingAddress;
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $customerBillingAddress;


    /**
     * @return string
     */
    public function getFlag(): string
    {
        return $this->flag.'';
    }

    /**
     * @param string|null $flag
     */
    public function setFlag(string $flag=null): void
    {
        $this->flag = $flag.'';
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment.'';
    }

    /**
     * @param string|null $comment
     */
    public function setComment(string $comment = null): void
    {
        $this->comment = $comment.'';
    }

    /**
     * @return bool
     */
    public function getIsOptimized(): bool
    {
        return (bool)$this->isOptimized;
    }

    /**
     * @param bool $isOptimized
     */
    public function setIsOptimized(bool $isOptimized=false): void
    {
        $this->isOptimized = $isOptimized;
    }

    /**
     * @return Customer|null
     */
    public function getRelatedCustomer(): ?Customer
    {
        return $this->relatedCustomer;
    }

    /**
     * @param mixed $relatedCustomer
     */
    public function setRelatedCustomer($relatedCustomer): void
    {
        $this->relatedCustomer = $relatedCustomer;
    }

    /**
     * @return mixed
     */
    public function getGrantedDiscount()
    {
        return $this->grantedDiscount;
    }

    /**
     * @param mixed $grantedDiscount
     */
    public function setGrantedDiscount($grantedDiscount): void
    {
        $this->grantedDiscount = $grantedDiscount;
    }

    /**
     * @return bool
     */
    public function getIsFixed(): bool
    {
        return (bool) $this->isFixed;
    }

    /**
     * @param bool $isFixed
     */
    public function setIsFixed(bool $isFixed=false): void
    {
        $this->isFixed = $isFixed;
    }

    /**
     * @return DateTime
     */
    public function getTargetTime(): ?DateTime
    {
        return $this->targetTime;
    }

    /**
     * @param DateTime|null $targetTime
     */
    public function setTargetTime(DateTime $targetTime = null): void
    {
        $this->targetTime = $targetTime;
    }

    /**
     * @return Document|null
     */
    public function getBill(): ?Document
    {
        return $this->bill;
    }

    /**
     * @param Document|null $bill
     */
    public function setBill(Document $bill =null): void
    {
        $this->bill = $bill;
    }

    /**
     * @return Document|null
     */
    public function getOrderConformation(): ?Document
    {
        return $this->orderConformation;
    }

    /**
     * @param Document|null $orderConformation
     */
    public function setOrderConformation(Document $orderConformation = null): void
    {
        $this->orderConformation = $orderConformation;
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


    public function __toString()
    {
        return 'Order #' .$this->getId();
    }

    /**
     * @return DateTime|null
     */
    public function getDateOrdered(): ?DateTime
    {
        return $this->dateOrdered;
    }

    /**
     * @param DateTime|null $dateOrdered
     */
    public function setDateOrdered(DateTime $dateOrdered = null): void
    {
        $this->dateOrdered = $dateOrdered;
    }

    /**
     * @return mixed
     */
    public function getCustomerShippingAddress()
    {
        return $this->customerShippingAddress;
    }

    /**
     * @param mixed $customerShippingAddress
     */
    public function setCustomerShippingAddress($customerShippingAddress): void
    {
        $this->customerShippingAddress = $customerShippingAddress;
    }

    /**
     * @return mixed
     */
    public function getCustomerBillingAddress()
    {
        return $this->customerBillingAddress;
    }

    /**
     * @param mixed $customerBillingAddress
     */
    public function setCustomerBillingAddress($customerBillingAddress): void
    {
        $this->customerBillingAddress = $customerBillingAddress;
    }

    /**
     * @return OrderedProductCategory
     */
    public function getRelatedOrderedCategorys(): ?OrderedProductCategory
    {
        return $this->relatedOrderedCategorys;
    }

    /**
     * @param OrderedProductCategory $relatedOrderedCategorys
     */
    public function setRelatedOrderedCategorys(OrderedProductCategory $relatedOrderedCategorys= null): void
    {
        $this->relatedOrderedCategorys = $relatedOrderedCategorys;
    }

    /**
     * @return bool
     */
    public function getIsRejected():bool
    {
        return (bool)$this->isRejected;
    }

    /**
     * @param bool $isRejected
     */
    public function setIsRejected(bool $isRejected = false): void
    {
        $this->isRejected = $isRejected;
    }
}