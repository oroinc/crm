<?php

namespace OroCRM\Bundle\MagentoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use OroCRM\Bundle\MagentoBundle\Entity\Order;

/**
 * @Route("/order")
 */
class OrderController extends Controller
{
    /**
     * @Route("/{id}", name="orocrm_magento_order_index", requirements={"id"="\d+"}))
     * @AclAncestor("orocrm_magento_order_view")
     * @Template
     */
    public function indexAction(Channel $channel)
    {
        return ['channelId' => $channel->getId()];
    }

    /**
     * @Route("/view/{id}", name="orocrm_magento_order_view", requirements={"id"="\d+"}))
     * @Acl(
     *      id="orocrm_magento_order_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMMagentoBundle:Order"
     * )
     * @Template
     */
    public function viewAction(Order $order)
    {
        return ['entity' => $order];
    }

    /**
     * @Route("/info/{id}", name="orocrm_magento_order_widget_info", requirements={"id"="\d+"}))
     * @AclAncestor("orocrm_magento_cart_view")
     * @Template
     */
    public function infoAction(Order $order)
    {
        return ['entity' => $order];
    }

    /**
     * @Route("/widget/grid/{id}", name="orocrm_magento_order_widget_items", requirements={"id"="\d+"}))
     * @AclAncestor("orocrm_magento_cart_view")
     * @Template
     */
    public function itemsAction(Order $order)
    {
        return ['entity' => $order];
    }
}
