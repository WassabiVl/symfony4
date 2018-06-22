<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 25.10.2017
 * Time: 13:02
 */
namespace app\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="product")
 */
class Product
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Batch", mappedBy="relatedProduct")
     */
    private $relatedBatch;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(type="string", unique=true, length=191)
     */
    private $slug;

    /**
     * @ORM\Column(type="decimal", precision=20, scale=4)
     */
    private $buyPrice;

    /**
     * @ORM\ManyToOne(targetEntity="ProductCategory", inversedBy="relatedProducts", cascade={"persist"}, fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private $productCategory;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\OrderedProducts", mappedBy="relatedProduct")
     */
    private $relatedOrderedProduct;

    /**
    * @ORM\Column(type="decimal", precision=20, scale=4)
    */
    private $halfLife;

    public function __construct()
    {
        $this->relatedOrderedProduct = new ArrayCollection();
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
    public function setName(string $name= null):void
    {
        $this->name = $name.'';
    }

    /**
     * @return mixed
     */
    public function getBuyPrice()
    {
        return $this->buyPrice;
    }

    /**
     * @param mixed $buyPrice
     */
    public function setBuyPrice($buyPrice):void
    {
        $this->buyPrice = $buyPrice;
    }

    /**
     * @return ProductCategory|null
     */
    public function getProductCategory(): ?ProductCategory
    {
        return $this->productCategory;
    }

    /**
     * @param ProductCategory $productCategory
     */
    public function setProductCategory(ProductCategory $productCategory=null):void
    {
        $this->productCategory = $productCategory;
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
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param mixed $slug
     */
    public function setSlug($slug):void
    {
        $this->slug = $slug;
    }

    /**
     * @return Batch
     */
    public function getRelatedBatch() :?batch
    {
        return $this->relatedBatch;
    }

    /**
     * @param Batch $relatedBatch
     */
    public function setRelatedBatch(Batch $relatedBatch):void
    {
        $this->relatedBatch = $relatedBatch;
    }



    public function __toString()
    {
        return $this->getProductCategory()->getName() . ' '. $this->getName().'';
    }

    /**
     * @return ArrayCollection|OrderedProducts[]
     */
    public function getRelatedOrderedProduct()
    {
        return $this->relatedOrderedProduct;
    }

    /**
     * @param OrderedProducts $relatedOrderedProduct
     * @return bool
     */
    public function addRelatedOrderedProduct(OrderedProducts $relatedOrderedProduct): bool
    {
        if (!$this->relatedOrderedProduct->contains($relatedOrderedProduct)){
            $this->relatedOrderedProduct[] = $relatedOrderedProduct;
            return true;
        }
        return false;
    }

    /**
     * @param OrderedProducts $relatedOrderedProduct
     * @return bool
     */
    public function removeRelatedOrderedProduct(OrderedProducts $relatedOrderedProduct): bool
    {
        if (!$this->relatedOrderedProduct->contains($relatedOrderedProduct)){
            return false;
        }
        $this->relatedOrderedProduct->remove($relatedOrderedProduct);
        $relatedOrderedProduct->setRelatedProduct(null);
        return true;
    }

    /**
     * @return mixed
     */
    public function getHalfLife()
    {
        return $this->halfLife;
    }

    /**
     * @param mixed $halfLife
     */
    public function setHalfLife($halfLife): void
    {
        $this->halfLife = $halfLife;
    }

}