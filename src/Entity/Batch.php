<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 25.10.2017
 * Time: 12:51
 */
namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="batch")
 */
class Batch
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Producer", inversedBy="relatedBatches")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private $relatedProducer;
    /**
     * @ORM\OneToOne(targetEntity="Product", inversedBy="relatedBatch", fetch="EAGER", cascade={"persist", "remove"})
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private $relatedProduct;
    /**
     * @ORM\Column(type="time")
     */
    private $dailyStartTime;
    /**
     * @ORM\Column(type="time")
     */
    private $dailyEndTime;
    /**
     * @ORM\Column(type="integer")
     */
    private $batchAmount;
    /**
     * @ORM\Column(type="decimal", scale=4, precision=20)
     */
    private $batchCost;

    /**
     * @ORM\OneToMany(targetEntity="BatchLockTime", mappedBy="relatedBatch", cascade={"persist","remove"}, fetch="EAGER", orphanRemoval=true)
     */
    private $relatedLockTimes;

    public function __construct()
    {
        $this->relatedLockTimes = new ArrayCollection();
    }

    /**
     * @return Producer|null
     */
    public function getRelatedProducer(): ?Producer
    {
        return $this->relatedProducer;
    }

    /**
     * @param Producer $relatedProducer
     */
    public function setRelatedProducer(Producer $relatedProducer): void
    {
        $this->relatedProducer = $relatedProducer;
    }

    /**
     * @return mixed
     */
    public function getDailyStartTime()
    {
        return $this->dailyStartTime;
    }

    /**
     * @param mixed $dailyStartTime
     */
    public function setDailyStartTime($dailyStartTime): void
    {
        $this->dailyStartTime = $dailyStartTime;
    }

    /**
     * @return mixed
     */
    public function getDailyEndTime()
    {
        return $this->dailyEndTime;
    }

    /**
     * @param mixed $dailyEndTime
     */
    public function setDailyEndTime($dailyEndTime): void
    {
        $this->dailyEndTime = $dailyEndTime;
    }

    /**
     * @return integer
     */
    public function getBatchAmount(): int
    {
        return (int)$this->batchAmount;
    }

    /**
     * @param integer $batchAmount
     */
    public function setBatchAmount(int $batchAmount = 0): void
    {
        $this->batchAmount = $batchAmount;
    }

    /**
     * @return mixed
     */
    public function getBatchCost()
    {
        return $this->batchCost;
    }

    /**
     * @param mixed $batchCost
     */
    public function setBatchCost($batchCost): void
    {
        $this->batchCost = $batchCost;
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
     * @return Product|null
     */
    public function getRelatedProduct(): ?Product
    {
        return $this->relatedProduct;
    }

    /**
     * @param Product $relatedProduct
     */
    public function setRelatedProduct(Product $relatedProduct): void
    {
         $this->relatedProduct = $relatedProduct;
    }


    /**
     * @return BatchLockTime|ArrayCollection
     */
    public function getRelatedLockTimes()
    {
        return $this->relatedLockTimes;
    }

    /**
     * @param BatchLockTime $lockTime
     * @return bool
     */
    public function addRelatedLockTime(BatchLockTime $lockTime): bool
    {
        if(!$this->relatedLockTimes->contains($lockTime)){
            $this->relatedLockTimes[] = $lockTime;
            $lockTime->setRelatedBatch($this);
            return true;
        }
        return false;
    }

    /**
     * @param BatchLockTime $lockTime
     * @return bool
     */
    public function removeRelatedLockTime(BatchLockTime $lockTime): bool
    {
        if(!$this->relatedLockTimes->contains($lockTime)){
            return false;
        }
        $this->relatedLockTimes->removeElement($lockTime);
        $lockTime->setRelatedBatch(null);
        return true;
    }

    public function __toString()
    {
        return 'Batch #'. $this->getId();
    }
}