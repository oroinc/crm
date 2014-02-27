<?php

namespace OroCRM\Bundle\MagentoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

/**
 * @Route("/cart")
 */
class CartController extends Controller
{
    /**
     * @Route("/{id}", name="orocrm_magento_cart_index", requirements={"id"="\d+"}))
     * @AclAncestor("orocrm_magento_cart_view")
     * @Template
     */
    public function indexAction(Channel $channel)
    {
        return ['channelId' => $channel->getId()];
    }

    /**
     * @Route("/view/{id}", name="orocrm_magento_cart_view", requirements={"id"="\d+"}))
     * @Acl(
     *      id="orocrm_magento_cart_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMMagentoBundle:Cart"
     * )
     * @Template
     */
    public function viewAction(Cart $cart)
    {
        return ['entity' => $cart];
    }

    /**
     * @Route("/info/{id}", name="orocrm_magento_cart_widget_info", requirements={"id"="\d+"}))
     * @AclAncestor("orocrm_magento_cart_view")
     * @Template
     */
    public function infoAction(Cart $cart)
    {
        return ['entity' => $cart];
    }

    /**
     * @Route("/widget/grid/{id}", name="orocrm_magento_cart_widget_items", requirements={"id"="\d+"}))
     * @AclAncestor("orocrm_magento_cart_view")
     * @Template
     */
    public function itemsAction(Cart $cart)
    {
        return ['entity' => $cart];
    }

    /**
     * @Route(
     *        "/widget/account_cart/{id}/{channelId}",
     *         name="orocrm_account_customer_cart_widget",
     *         requirements={"id"="\d+", "channelId"="\d+"}
     * )
     * @AclAncestor("orocrm_magento_cart_view")
     * @Template
     */
    public function accountCustomerCartInfoAction($id, $channelId)
    {
        return array('customerId' => $id, 'channelId' => $channelId);
    }
}
