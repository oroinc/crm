<?php

namespace OroCRM\Bundle\MagentoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Order;

/**
 * @Route("/order")
 */
class OrderController extends Controller
{
    /**
     * @Route("/", name="orocrm_magento_order_index")
     * @AclAncestor("orocrm_magento_order_view")
     * @Template
     */
    public function indexAction()
    {
        return [
            'entity_class' => $this->container->getParameter('orocrm_magento.order.entity.class')
        ];
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

    /**
     * @Route(
     *        "/account-widget/customer-orders/{customerId}/{channelId}",
     *        name="orocrm_magento_widget_customer_orders",
     *        requirements={"customerId"="\d+", "channelId"="\d+"}
     * )
     * @AclAncestor("orocrm_magento_order_view")
     * @ParamConverter("customer", class="OroCRMMagentoBundle:Customer", options={"id"="customerId"})
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id"="channelId"})
     * @Template
     */
    public function customerOrdersAction(Customer $customer, Channel $channel)
    {
        return array('customer' => $customer, 'channel' => $channel);
    }

    /**
     * @Route(
     *        "/customer-widget/customer-orders/{customerId}/{channelId}",
     *        name="orocrm_magento_customer_orders_widget",
     *        requirements={"customerId"="\d+", "channelId"="\d+"}
     * )
     * @ParamConverter("customer", class="OroCRMMagentoBundle:Customer", options={"id"="customerId"})
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id"="channelId"})
     * @Template
     */
    public function customerOrdersWidgetAction(Customer $customer, Channel $channel)
    {
        return array('customer' => $customer, 'channel' => $channel);
    }

    /**
     * @Route("/actualize/{id}", name="orocrm_magento_order_actualize", requirements={"id"="\d+"}))
     * @AclAncestor("orocrm_magento_order_view")
     */
    public function actualizeAction(Order $order)
    {
        $result = false;

        try {
            $processor = $this->get('oro_integration.sync.processor');
            $result = $processor->process(
                $order->getChannel(),
                'order',
                ['filters' => ['increment_id' => $order->getIncrementId()]]
            );
        } catch (\LogicException $e) {
            $this->get('logger')->addCritical($e->getMessage(), ['exception' => $e]);
        }

        if ($result === true) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('orocrm.magento.controller.synchronization_success')
            );
        } else {
            $this->get('session')->getFlashBag()->add(
                'error',
                $this->get('translator')->trans('orocrm.magento.controller.synchronization_error')
            );
        }

        return $this->redirect($this->generateUrl('orocrm_magento_order_view', ['id' => $order->getId()]));
    }
}
