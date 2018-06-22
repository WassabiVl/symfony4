<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 25.10.2017
 * Time: 11:55
 */
namespace app\Entity;


use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="discount_group")
 */
class DiscountGroup
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $dateTimeStart;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $dateTimeEnd;


    /**
     * @ORM\Column(type="decimal", precision=20, scale=4)
     */
    private $discountInPercent;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isActive;

    /**
     * @ORM\OneToMany(targetEntity="Customer", mappedBy="discountGroup")
     */
    private $relatedCustomers;


    public function __construct()
    {
        $this->relatedCustomers = new ArrayCollection();
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
     * @return string
     */
    public function getName():string
    {
        return $this->name.'';
    }

    /**
     * @param string $name
     */
    public function setName(string $name=null):void
    {
        $this->name = $name.'';
    }

    /**
     * @return bool
     */
    public function getIsActive():bool
    {
        return (bool)$this->isActive;
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive(Bool $isActive = false):void
    {
        $this->isActive = $isActive;
    }

    /**
     * @return ArrayCollection|Customer[]
     */
    public function getRelatedCustomers()
    {
        return $this->relatedCustomers;
    }

    /**
     * @param Customer $customer
     * @return bool
     *
     */
    public function addRelatedCustomer(Customer $customer):bool
    {
        if(!$this->relatedCustomers->contains($customer)){
            $this->relatedCustomers[] = $customer;
            $customer->setDiscountGroup($this);
            return true;
        }
        return false;
    }

    /**
     * @param Customer $customer
     * @return bool
     */
    public function removeRelatedCustomer(Customer $customer):bool
    {
        if(!$this->relatedCustomers->contains($customer)){
            return false;
        }
        $this->relatedCustomers->removeElement($customer);
        $customer->setDiscountGroup(null);
        return true;
    }

    public function __toString()
    {
        return $this->getName().'';
    }

    /**
     * @return mixed
     */
    public function getDiscountInPercent()
    {
        return $this->discountInPercent;
    }

    /**
     * @param mixed $discountInPercent
     */
    public function setDiscountInPercent(float $discountInPercent =null): void
    {
        $this->discountInPercent = $discountInPercent;
    }

    /**
     * @return DateTime|null
     */
    public function getDateTimeStart(): ?DateTime
    {
        return $this->dateTimeStart;
    }

    /**
     * @param DateTime $dateTimeStart
     */
    public function setDateTimeStart(DateTime $dateTimeStart =null): void
    {
        $this->dateTimeStart = $dateTimeStart;
    }

    /**
     * @return DateTime|null
     */
    public function getDateTimeEnd(): ?DateTime
    {
        return $this->dateTimeEnd;
    }

    /**
     * @param DateTime $dateTimeEnd
     */
    public function setDateTimeEnd(DateTime $dateTimeEnd =null): void
    {
        $this->dateTimeEnd = $dateTimeEnd;
    }

}