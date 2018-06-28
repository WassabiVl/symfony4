<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 25.10.2017
 * Time: 13:00
 */
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="batch_lock_times")
 */
class BatchLockTime
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;
    /**
     * @ORM\Column(type="datetime")
     */
    private $startTime;
    /**
     * @ORM\Column(type="datetime")
     */
    private $endTime;
    /**
     * @ORM\ManyToOne(targetEntity="Batch", inversedBy="relatedLockTimes")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $relatedBatch;

    /**
     * @return DateTime|null
     */
    public function getStartTime():?DateTime
    {
        return $this->startTime;
    }

    /**
     * @param DateTime $startTime
     */
    public function setStartTime(DateTime $startTime): void
    {
        $this->startTime = $startTime;
    }

    /**
     * @return DateTime|null
     */
    public function getEndTime(): ?DateTime
    {
        return $this->endTime;
    }

    /**
     * @param DateTime $endTime
     */
    public function setEndTime(DateTime $endTime): void
    {
        $this->endTime = $endTime;
    }

    /**
     * @return Batch
     */
    public function getRelatedBatch(): ?Batch
    {
        return $this->relatedBatch;
    }

    /**
     * @param Batch $relatedBatch
     */
    public function setRelatedBatch(Batch $relatedBatch): void
    {
        $this->relatedBatch = $relatedBatch;
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
     * @return string
     */
    public function __toString(): string
    {
        return 'Batch Lock Time' . $this->getId();
    }


}