<?php

namespace OroCRM\Bundle\B2CMockBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use OroCRM\Bundle\B2CMockBundle\Entity\SaleOrder;

/**
 * @Route("/order")
 */
class OrderController extends Controller
{
    /**
     * @Route(name="orocrm_b2c_order_index")
     * @Template
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/view/{id}", name="orocrm_b2c_order_view", requirements={"id"="\d+"})
     * @Template
     */
    public function viewAction(SaleOrder $order)
    {
        return array('entity' => $order);
    }

    /**
     * @Route("/info/{id}", name="orocrm_b2c_order_info", requirements={"id"="\d+"})
     * @Template
     */
    public function infoAction(SaleOrder $order)
    {
        return array('entity' => $order);
    }
}
