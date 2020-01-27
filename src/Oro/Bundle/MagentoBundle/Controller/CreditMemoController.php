<?php

namespace Oro\Bundle\MagentoBundle\Controller;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\CreditMemo;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The controller for Magento CreditMemo entity.
 * @Route("/credit-memo")
 */
class CreditMemoController extends Controller
{
    /**
     * @Route("/", name="oro_magento_credit_memo_index")
     * @AclAncestor("oro_magento_credit_memo_view")
     * @Template
     */
    public function indexAction()
    {
        return [
            'entity_class' => CreditMemo::class
        ];
    }

    /**
     * @Route("/view/{id}", name="oro_magento_credit_memo_view", requirements={"id"="\d+"}))
     * @Acl(
     *      id="oro_magento_credit_memo_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroMagentoBundle:CreditMemo"
     * )
     * @Template
     * @param CreditMemo $entity
     *
     * @return array
     */
    public function viewAction(CreditMemo $entity)
    {
        return ['entity' => $entity];
    }

    /**
     * @Route("/info/{id}", name="oro_magento_credit_memo_widget_info", requirements={"id"="\d+"}))
     * @AclAncestor("oro_magento_credit_memo_view")
     * @Template
     * @param CreditMemo $entity
     *
     * @return array
     */
    public function infoAction(CreditMemo $entity)
    {
        return ['entity' => $entity];
    }

    /**
     * @Route("/widget/grid/{id}", name="oro_magento_credit_memo_widget_items", requirements={"id"="\d+"}))
     * @AclAncestor("oro_magento_credit_memo_view")
     * @Template
     * @param CreditMemo $entity
     * @return array
     */
    public function itemsAction(CreditMemo $entity)
    {
        return ['entity' => $entity];
    }

    /**
     * @Route(
     *        "/account-widget/customer_credit_memo/{customerId}/{channelId}",
     *         name="oro_magento_widget_customer_credit_memo",
     *         requirements={"customerId"="\d+", "channelId"="\d+"}
     * )
     * @AclAncestor("oro_magento_credit_memo_view")
     * @ParamConverter("customer", class="OroMagentoBundle:Customer", options={"id" = "customerId"})
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id" = "channelId"})
     * @Template
     * @param Customer $customer
     * @param Channel $channel
     * @return array
     */
    public function customerCreditMemosAction(Customer $customer, Channel $channel)
    {
        return ['customer' => $customer, 'channel' => $channel];
    }

    /**
     * @Route(
     *        "/widget/customer_credit_memo/{customerId}/{channelId}",
     *         name="oro_magento_customer_credit_memo_widget",
     *         requirements={"customerId"="\d+", "channelId"="\d+"}
     * )
     * @AclAncestor("oro_magento_credit_memo_view")
     * @ParamConverter("customer", class="OroMagentoBundle:Customer", options={"id" = "customerId"})
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id" = "channelId"})
     * @Template
     * @param Customer $customer
     * @param Channel $channel
     * @return array
     */
    public function customerCreditMemosWidgetAction(Customer $customer, Channel $channel)
    {
        return ['customer' => $customer, 'channel' => $channel];
    }

    /**
     * @Route(
     *        "/widget/order_credit_memo/{orderId}",
     *         name="oro_magento_order_credit_memo_widget",
     *         requirements={"orderId"="\d+"}
     * )
     * @AclAncestor("oro_magento_credit_memo_view")
     * @ParamConverter("order", class="OroMagentoBundle:Order", options={"id" = "orderId"})
     * @Template
     * @param Order $order
     * @return array
     */
    public function orderCreditMemosWidgetAction($order)
    {
        return ['order' => $order];
    }
}
