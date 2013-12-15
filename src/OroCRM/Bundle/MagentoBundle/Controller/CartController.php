<?php

namespace OroCRM\Bundle\MagentoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;

/**
 * @Route("/cart")
 */
class CartController extends Controller
{
    /**
     * @Route("/")
     * @AclAncestor("orocrm_magento_cart_view")
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/view/{id}", requirements={"id"="\d+"}))
     * @Acl(
     *      id="orocrm_magento_cart_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMMagentoBundle:Cart"
     * )
     * @Template()
     */
    public function viewAction(Cart $cart)
    {
        return ['entity' => $cart];
    }

    /**
     * @Route("/info/{id}", name="orocrm_cart_widget_info", requirements={"id"="\d+"}))
     * @AclAncestor("orocrm_magento_cart_view")
     * @Template()
     */
    public function infoAction(Cart $cart)
    {
        return ['entity' => $cart];
    }

    /**
     * @Route("/widget/grid/{id}", name="orocrm_cart_widget_items", requirements={"id"="\d+"}))
     * @AclAncestor("orocrm_magento_cart_view")
     * @Template("OroCRMMagentoBundle:Cart:cartItems.html.twig")
     */
    public function gridAction(Cart $cart)
    {
        return ['entity' => $cart];
    }
}
