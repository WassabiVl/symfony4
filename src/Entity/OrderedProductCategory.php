<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 25.10.2017
 * Time: 13:37
 */
namespace app\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ordered_product_category")
 */
class OrderedProductCategory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="Order", inversedBy="relatedOrderedCategorys" ,cascade={"persist"}, fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private $relatedOrder;

    /**
     * @ORM\ManyToOne(targetEntity="ProductCategory", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private $relatedProductCategory;

    /**
     * @ORM\Column(type="decimal", precision=20, scale=4, nullable=true)
     */
    private $relatedBulkDiscount;

    /**
     * @ORM\Column(type="decimal", precision=20, scale=4, nullable=false)
     */
    private $relatedBuyPrice;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $orderedAmount;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $deliveredAmount;

    /**
     * @ORM\OneToOne(targetEntity="Document",cascade={"persist", "remove", "merge"})
     * @ORM\JoinColumn(name="related_adr_document", referencedColumnName="id", nullable=true)
     */
    private $ADRDocument;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\OrderedProducts", mappedBy="orderedProductCategory", cascade={"remove"})
     */
    private $relatedOrderedProduct;


    /**
     * @return Order|null
     */
    public function getRelatedOrder(): ?Order
    {
        return $this->relatedOrder;
    }

    /**
     * @param Order|null $relatedOrder
     */
    public function setRelatedOrder(Order $relatedOrder=null):void
    {
        $this->relatedOrder = $relatedOrder;
    }

    /**
     * @return ProductCategory|null
     */
    public function getRelatedProductCategory(): ?ProductCategory
    {
        return $this->relatedProductCategory;
    }

    /**
     * @param ProductCategory|null $relatedProductCategory
     */
    public function setRelatedProductCategory(ProductCategory $relatedProductCategory =null):void
    {
        $this->relatedProductCategory = $relatedProductCategory;
    }


    /**
     * @return int
     */
    public function getOrderedAmount(): int
    {
        return (int)$this->orderedAmount;
    }

    /**
     * @param int|null $orderedAmount
     */
    public function setOrderedAmount(int $orderedAmount = 0): void
    {
        $this->orderedAmount = $orderedAmount;
    }

    /**
     * @return int
     */
    public function getDeliveredAmount(): int
    {
        return (int)$this->deliveredAmount;
    }

    /**
     * @param int|null $deliveredAmount
     */
    public function setDeliveredAmount(int $deliveredAmount = 0): void
    {
        $this->deliveredAmount = $deliveredAmount;
    }

    /**
     * @return Document|null
     */
    public function getADRDocument(): ?Document
    {
        return $this->ADRDocument;
    }

    /**
     * @param Document $ADRDocument
     */
    public function setADRDocument(Document $ADRDocument=null):void
    {
        $this->ADRDocument = $ADRDocument;
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
     * @return mixed
     */
    public function getRelatedBulkDiscount()
    {
        return $this->relatedBulkDiscount;
    }

    /**
     * @param mixed $relatedBulkDiscount
     */
    public function setRelatedBulkDiscount($relatedBulkDiscount):void
    {
        $this->relatedBulkDiscount = $relatedBulkDiscount;
    }

    /**
     * @return mixed
     */
    public function getRelatedBuyPrice()
    {
        return $this->relatedBuyPrice;
    }

    /**
     * @param mixed $relatedBuyPrice
     */
    public function setRelatedBuyPrice($relatedBuyPrice): void
    {
        $this->relatedBuyPrice = $relatedBuyPrice;
    }

    /**
     * @return OrderedProducts|null
     */
    public function getRelatedOrderedProduct(): ?OrderedProducts
    {
        return $this->relatedOrderedProduct;
    }

    /**
     * @param OrderedProducts|null $relatedOrderedProduct
     */
    public function setRelatedOrderedProduct(OrderedProducts $relatedOrderedProduct = null): void
    {
        $this->relatedOrderedProduct = $relatedOrderedProduct;
    }

    public function __toString()
    {
        return 'Ordered Product Category #' . $this->id;
    }
}