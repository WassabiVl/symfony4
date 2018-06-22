<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 25.10.2017
 * Time: 13:03
 */
namespace app\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="product_category")
 */
class ProductCategory
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
    private $name;

    /**
     * @ORM\Column(type="decimal", precision=20, scale=4)
     */
    private $sellPrice;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Product", mappedBy="productCategory")
     */
    private $relatedProducts;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ProductSellSizes", mappedBy="relatedProductCategory")
     */
    private $relatedProductSellSizes;

    public function __construct()
    {
        $this->relatedProducts = new ArrayCollection();
        $this->relatedProductSellSizes = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name.'';
    }

    /**
     * @param string|null $name
     */
    public function setName(string $name = null):void
    {
        $this->name = $name.'';
    }

    /**
     * @return mixed
     */
    public function getSellPrice()
    {
        return $this->sellPrice;
    }

    /**
     * @param mixed $sellPrice
     */
    public function setSellPrice($sellPrice):void
    {
        $this->sellPrice = $sellPrice;
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
     * @return ArrayCollection|Product[]
     */
    public function getRelatedProducts()
    {
        return $this->relatedProducts;
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function addRelatedProduct(Product $product): bool
    {
        if(!$this->relatedProducts->contains($product)){
            $this->relatedProducts[] = $product;
            $product->setProductCategory($this);
            return true;
        }
        return false;
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function removeRelatedProduct(Product $product): bool
    {
        if(!$this->relatedProducts->contains($product)){
            return false;
        }
        $this->relatedProducts->removeElement($product);
        $product->setProductCategory(null);
        return true;
    }


    /**
     * @return ArrayCollection|ProductSellSizes[]
     */
    public function getRelatedProductSellSizes()
    {
        return $this->relatedProductSellSizes;
    }

    /**
     * @param ProductSellSizes $productSellSizes
     * @return bool
     */
    public function addRelatedProductSellSize(ProductSellSizes $productSellSizes): bool
    {
        if(!$this->relatedProductSellSizes->contains($productSellSizes)){
            $this->relatedProductSellSizes[] = $productSellSizes;
            $productSellSizes->setRelatedProductCategory($this);
            return true;
        }
        return false;
    }

    /**
     * @param ProductSellSizes $productSellSizes
     * @return bool
     */
    public function removeRelatedProductSellSize(ProductSellSizes $productSellSizes): bool
    {
        if(!$this->relatedProductSellSizes->contains($productSellSizes)){
            return false;
        }
        $this->relatedProductSellSizes->removeElement($productSellSizes);
        $productSellSizes->setRelatedProductCategory(null);
        return true;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName().'';
    }

}