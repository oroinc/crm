<?php

namespace Oro\Bundle\MagentoBundle\Controller;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The controller for Magento Cart entity.
 * @Route("/cart")
 */
class CartController extends Controller
{
    /**
     * @Route("/", name="oro_magento_cart_index")
     * @AclAncestor("oro_magento_cart_view")
     * @Template
     */
    public function indexAction()
    {
        return [
            'entity_class' => Cart::class
        ];
    }

    /**
     * @Route("/view/{id}", name="oro_magento_cart_view", requirements={"id"="\d+"}))
     * @Acl(
     *      id="oro_magento_cart_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroMagentoBundle:Cart"
     * )
     * @Template
     */
    public function viewAction(Cart $cart)
    {
        return ['entity' => $cart];
    }

    /**
     * @Route("/info/{id}", name="oro_magento_cart_widget_info", requirements={"id"="\d+"}))
     * @AclAncestor("oro_magento_cart_view")
     * @Template
     */
    public function infoAction(Cart $cart)
    {
        return ['entity' => $cart];
    }

    /**
     * @Route(
     *      "/widget/grid/{id}/{isRemoved}",
     *      name="oro_magento_cart_widget_items",
     *      requirements={"id"="\d+", "isRemoved"="\d+"}
     * )
     * @AclAncestor("oro_magento_cart_view")
     * @ParamConverter("cart", class="OroMagentoBundle:Cart", options={"id" = "id"})
     * @Template
     */
    public function itemsAction(Cart $cart, $isRemoved = false)
    {
        return ['entity' => $cart, 'is_removed' => (bool)$isRemoved];
    }

    /**
     * @Route(
     *        "/widget/account_cart/{customerId}/{channelId}",
     *         name="oro_magento_widget_customer_carts",
     *         requirements={"customerId"="\d+", "channelId"="\d+"}
     * )
     * @AclAncestor("oro_magento_cart_view")
     * @ParamConverter("customer", class="OroMagentoBundle:Customer", options={"id" = "customerId"})
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id" = "channelId"})
     * @Template
     */
    public function customerCartsAction(Customer $customer, Channel $channel)
    {
        return array('customer' => $customer, 'channel' => $channel);
    }

    /**
     * @Route(
     *        "/widget/customer_cart/{customerId}/{channelId}",
     *         name="oro_magento_customer_carts_widget",
     *         requirements={"customerId"="\d+", "channelId"="\d+"}
     * )
     * @AclAncestor("oro_magento_cart_view")
     * @ParamConverter("customer", class="OroMagentoBundle:Customer", options={"id" = "customerId"})
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id" = "channelId"})
     * @Template
     */
    public function customerCartsWidgetAction(Customer $customer, Channel $channel)
    {
        return array('customer' => $customer, 'channel' => $channel);
    }

    /**
     * @Route("/actualize/{id}", name="oro_magento_cart_actualize", requirements={"id"="\d+"}))
     * @AclAncestor("oro_magento_cart_view")
     */
    public function actualizeAction(Cart $cart)
    {
        $result = false;
        $connector = $this->get('oro_magento.mage.cart_connector');

        try {
            $processor = $this->get('oro_integration.sync.processor');
            $result = $processor->process(
                $cart->getChannel(),
                $connector->getType(),
                ['filters' => ['entity_id' => $cart->getOriginId()]]
            );
        } catch (\LogicException $e) {
            $this->get('logger')->addCritical($e->getMessage(), ['exception' => $e]);
        }

        if ($result === true) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('oro.magento.controller.synchronization_success')
            );
        } else {
            $this->get('session')->getFlashBag()->add(
                'error',
                $this->get('translator')->trans('oro.magento.controller.synchronization_error')
            );
        }

        return $this->redirect($this->generateUrl('oro_magento_cart_view', ['id' => $cart->getId()]));
    }
}
