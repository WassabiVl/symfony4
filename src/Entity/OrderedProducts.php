<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 25.10.2017
 * Time: 13:44
 */
namespace app\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ordered_products")
 */
class OrderedProducts
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $amount;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $trackingLink;
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $producerAddress;
    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="relatedOrderedProduct")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private $relatedProduct;
    
    /**
     * @ORM\OneToOne(targetEntity="OrderedProductCategory", inversedBy="relatedOrderedProduct", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $orderedProductCategory;

    /**
     * @return integer
     */
    public function getAmount(): int
    {
        return (int)$this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount(int $amount = 0):void
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getTrackingLink(): string
    {
        return $this->trackingLink.'';
    }

    /**
     * @param string|null $trackingLink
     */
    public function setTrackingLink(String $trackingLink = null):void
    {
        $this->trackingLink = $trackingLink.'';
    }

    /**
     * @return Product|null
     */
    public function getRelatedProduct(): ?Product
    {
        return $this->relatedProduct;
    }

    /**
     * @param Product $relatedProduct
     */
    public function setRelatedProduct(Product $relatedProduct):void
    {
        $this->relatedProduct = $relatedProduct;
    }

    /**
     * @return OrderedProductCategory|null
     */
    public function getOrderedProductCategory(): ?OrderedProductCategory
    {
        return $this->orderedProductCategory;
    }

    /**
     * @param OrderedProductCategory $orderedProductCategory
     */
    public function setOrderedProductCategory(OrderedProductCategory $orderedProductCategory):void
    {
        $this->orderedProductCategory = $orderedProductCategory;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getProducerAddress(): string
    {
        return $this->producerAddress.'';
    }

    /**
     * @param string|null $producerAddress
     */
    public function setProducerAddress(string $producerAddress=null): void
    {
        $this->producerAddress = $producerAddress.'';
    }

    public function __toString()
    {
        return 'Ordered Product #'. $this->getId();
    }
}