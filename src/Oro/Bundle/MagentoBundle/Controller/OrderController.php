<?php

namespace Oro\Bundle\MagentoBundle\Controller;

use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The controller for Magento Order entity.
 * @Route("/order")
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class OrderController extends Controller
{
    /**
     * @Route("/", name="oro_magento_order_index")
     * @AclAncestor("oro_magento_order_view")
     * @Template
     */
    public function indexAction()
    {
        return [
            'entity_class' => Order::class
        ];
    }

    /**
     * @Route("/view/{id}", name="oro_magento_order_view", requirements={"id"="\d+"}))
     * @Acl(
     *      id="oro_magento_order_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroMagentoBundle:Order"
     * )
     * @Template
     * @param Order $order
     * @return array
     */
    public function viewAction(Order $order)
    {
        return ['entity' => $order];
    }

    /**
     * @Route("/info/{id}", name="oro_magento_order_widget_info", requirements={"id"="\d+"}))
     * @AclAncestor("oro_magento_cart_view")
     * @Template
     * @param Order $order
     * @return array
     */
    public function infoAction(Order $order)
    {
        return ['entity' => $order];
    }

    /**
     * @Route("/widget/grid/{id}", name="oro_magento_order_widget_items", requirements={"id"="\d+"}))
     * @AclAncestor("oro_magento_cart_view")
     * @Template
     * @param Order $order
     * @return array
     */
    public function itemsAction(Order $order)
    {
        return ['entity' => $order];
    }

    /**
     * @Route(
     *        "/account-widget/customer-orders/{customerId}/{channelId}",
     *        name="oro_magento_widget_customer_orders",
     *        requirements={"customerId"="\d+", "channelId"="\d+"}
     * )
     * @AclAncestor("oro_magento_order_view")
     * @ParamConverter("customer", class="OroMagentoBundle:Customer", options={"id"="customerId"})
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id"="channelId"})
     * @Template
     * @param Customer $customer
     * @param Channel $channel
     * @return array
     */
    public function customerOrdersAction(Customer $customer, Channel $channel)
    {
        return ['customer' => $customer, 'channel' => $channel];
    }

    /**
     * @Route(
     *        "/account-widget/customer-recent-purchases/{customerId}/{channelId}",
     *        name="oro_magento_widget_customer_recent_purchases",
     *        requirements={"customerId"="\d+", "channelId"="\d+"}
     * )
     * @AclAncestor("oro_magento_order_view")
     * @ParamConverter("customer", class="OroMagentoBundle:Customer", options={"id"="customerId"})
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id"="channelId"})
     * @Template
     * @param Customer $customer
     * @param Channel $channel
     * @return array
     */
    public function customerRecentPurchasesAction(Customer $customer, Channel $channel)
    {
        return ['customer' => $customer, 'channel' => $channel];
    }

    /**
     * @Route(
     *        "/customer-widget/customer-orders/{customerId}/{channelId}",
     *        name="oro_magento_customer_orders_widget",
     *        requirements={"customerId"="\d+", "channelId"="\d+"}
     * )
     * @ParamConverter("customer", class="OroMagentoBundle:Customer", options={"id"="customerId"})
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id"="channelId"})
     * @Template
     * @param Customer $customer
     * @param Channel $channel
     * @return array
     */
    public function customerOrdersWidgetAction(Customer $customer, Channel $channel)
    {
        return ['customer' => $customer, 'channel' => $channel];
    }

    /**
     * @Route(
     *        "/customer-widget/customer-recent-purchases/{customerId}/{channelId}",
     *        name="oro_magento_customer_recent_purchases_widget",
     *        requirements={"customerId"="\d+", "channelId"="\d+"}
     * )
     * @ParamConverter("customer", class="OroMagentoBundle:Customer", options={"id"="customerId"})
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id"="channelId"})
     * @Template
     * @param Customer $customer
     * @param Channel $channel
     * @return array
     */
    public function customerRecentPurchasesWidgetAction(Customer $customer, Channel $channel)
    {
        return ['customer' => $customer, 'channel' => $channel];
    }

    /**
     * @Route(
     *        "/account-widget/order-notes/{customerId}/{channelId}",
     *        name="oro_magento_widget_customer_order_notes",
     *        requirements={"customerId"="\d+", "channelId"="\d+"}
     * )
     * @AclAncestor("oro_magento_order_view")
     * @ParamConverter("customer", class="OroMagentoBundle:Customer", options={"id"="customerId"})
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id"="channelId"})
     * @Template
     * @param Customer $customer
     * @param Channel $channel
     * @return array
     */
    public function customerOrderNotesAction(Customer $customer, Channel $channel)
    {
        return ['customer' => $customer, 'channel' => $channel];
    }

    /**
     * @Route(
     *        "/customer-widget/order-notes/{customerId}/{channelId}",
     *        name="oro_magento_customer_order_notes_widget",
     *        requirements={"customerId"="\d+", "channelId"="\d+"}
     * )
     * @AclAncestor("oro_magento_order_view")
     * @ParamConverter("customer", class="OroMagentoBundle:Customer", options={"id"="customerId"})
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id"="channelId"})
     * @Template
     * @param Customer $customer
     * @param Channel $channel
     * @return array
     */
    public function customerOrderNotesWidgetAction(Customer $customer, Channel $channel)
    {
        return ['customer' => $customer, 'channel' => $channel];
    }

    /**
     * @Route(
     *        "/widget/order_notes/{orderId}",
     *         name="oro_magento_order_notes_widget",
     *         requirements={"orderId"="\d+"}
     * )
     * @AclAncestor("oro_magento_order_view")
     * @ParamConverter("order", class="OroMagentoBundle:Order", options={"id" = "orderId"})
     * @Template
     * @param Order $order
     * @return array
     */
    public function orderNotesWidgetAction($order)
    {
        return ['order' => $order];
    }

    /**
     * @Route("/actualize/{id}", name="oro_magento_order_actualize", requirements={"id"="\d+"}))
     * @AclAncestor("oro_magento_order_view")
     * @param Order $order
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function actualizeAction(Order $order)
    {
        $result = false;

        try {
            $result = $this->loadOrderInformation(
                $order->getChannel(),
                [
                    ProcessorRegistry::TYPE_IMPORT => [
                        'filters' => [
                            'increment_id' => $order->getIncrementId()
                        ],
                        'complex_filters' => [
                            'updated_at-gt' => null,
                            'updated_at-lte' => null
                        ]
                    ]
                ]
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

        return $this->redirect($this->generateUrl('oro_magento_order_view', ['id' => $order->getId()]));
    }

    /**
     * @param Channel $channel
     * @param array $configuration
     * @return bool
     */
    protected function loadOrderInformation(Channel $channel, array $configuration = [])
    {
        $orderInformationLoader = $this->get('oro_magento.service.order.information_loader');

        return $orderInformationLoader->load($channel, $configuration);
    }
}
