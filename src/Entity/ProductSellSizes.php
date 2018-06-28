<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 25.10.2017
 * Time: 13:18
 */
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="product_sell_sizes")
 */
class ProductSellSizes
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;
    /**
     * @ORM\Column(type="integer")
     */
    private $amount;
    /**
     * @ORM\Column(type="decimal", precision=7, scale=4)
     */
    private $discountInPercent;
    /**
     * @ORM\ManyToOne(targetEntity="ProductCategory", inversedBy="relatedProductSellSizes", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $relatedProductCategory;

    /**
     * @return integer
     */
    public function getAmount(): int
    {
        return (int)$this->amount;
    }

    /**
     * @param integer|null $amount
     */
    public function setAmount(int $amount = 0): void
    {
        $this->amount = $amount;
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
    public function setDiscountInPercent($discountInPercent): void
    {
        $this->discountInPercent = $discountInPercent;
    }

    /**
     * @return ProductCategory|null
     */
    public function getRelatedProductCategory(): ?ProductCategory
    {
        return $this->relatedProductCategory;
    }

    /**
     * @param ProductCategory $relatedProductCategory
     */
    public function setRelatedProductCategory(ProductCategory $relatedProductCategory=null): void
    {
        $this->relatedProductCategory = $relatedProductCategory;
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

    



}