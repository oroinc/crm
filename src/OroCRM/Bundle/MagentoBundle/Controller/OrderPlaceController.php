<?php

namespace OroCRM\Bundle\MagentoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;

/**
 * @Route("/order/place")
 */
class OrderPlaceController extends Controller
{
    /**
     * @Route("/cart/{id}", name="orocrm_magento_orderplace_cart", requirements={"id"="\d+"}))
     * @Template
     * TODO add ACL
     */
    public function cartAction(Cart $cart)
    {
        return [];
    }
}
