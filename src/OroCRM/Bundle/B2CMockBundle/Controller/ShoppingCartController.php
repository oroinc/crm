<?php

namespace OroCRM\Bundle\B2CMockBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use OroCRM\Bundle\B2CMockBundle\Entity\ShoppingCart;

/**
 * @Route("/shopping_cart", name="orocrm_call_create")
 */
class ShoppingCartController extends Controller
{
    /**
     * @Route(name="orocrm_b2c_shopping_cart_index")
     * @Template
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/view/{id}", name="orocrm_b2c_shopping_cart_view", requirements={"id"="\d+"})
     * @Template
     */
    public function viewAction(ShoppingCart $cart)
    {
        return array('entity' => $cart);
    }
}
