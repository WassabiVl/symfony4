<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 08.11.2017
 * Time: 13:05
 */

namespace App\Utils;


use Doctrine\Common\Collections\ArrayCollection;

class ErrorHandler
{
    /**
     * @var ArrayCollection|array[]
     */
    private $errorCollection;

    public function __construct()
    {
        $this->errorCollection = new ArrayCollection();
    }


    /**
     * @param $errorMessage
     * @param $cssClass
     */
    public function add($errorMessage, $cssClass): void
    {
        $this->errorCollection->add(array('errorMessage' => $errorMessage, 'cssClass' => $cssClass));
    }

    /**
     * @return ArrayCollection|\array[]
     */
    public function get()
    {
        return $this->errorCollection;
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return ($this->errorCollection->count()>0);
    }

}