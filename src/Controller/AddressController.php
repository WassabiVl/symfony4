<?php
/**
 * Created by PhpStorm.
 * User: giese
 * Date: 02.11.2017
 * Time: 10:46
 */

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AddressController extends Controller
{

    public function createAddress(Product $product){

        $em = $this->getDoctrine()->getManager();
        
        $em->persist($product);
        $em->flush();
        return $product->getId();
    }

}