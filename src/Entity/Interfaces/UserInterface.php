<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 24.11.2017
 * Time: 15:54
 */

namespace AppBundle\Entity\Interfaces;


interface UserInterface
{
    public function getRelatedAccount();
    public function setRelatedAccount($relatedAccount);
    
}