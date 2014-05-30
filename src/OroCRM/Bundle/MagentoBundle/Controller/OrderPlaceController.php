<?php

namespace OroCRM\Bundle\MagentoBundle\Controller;

use Doctrine\ORM\EntityManager;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\IntegrationBundle\Model\IntegrationEntityTrait;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

/**
 * @Route("/order/place")
 */
class OrderPlaceController extends Controller
{
    const SYNC_SUCCESS    = 'success';
    const SYNC_ERROR      = 'error';

    /**
     * @Route("/cart/{id}", name="orocrm_magento_orderplace_cart", requirements={"id"="\d+"}))
     * @AclAncestor("oro_workflow")
     * @Template("OroCRMMagentoBundle:OrderPlace:place.html.twig")
     */
    public function cartAction(Cart $cart)
    {
        $urlGenerator = $this
            ->get('orocrm_magento.service.magento_url_generator')
            ->setChannel($cart->getChannel())
            ->setFlowName('oro_sales_new_order')
            ->setOrigin('quote')
            ->generate(
                $cart->getOriginId(),
                'orocrm_magento_orderplace_success',
                'orocrm_magento_orderplace_error'
            );

        $translator = $this->get('translator');

        return [
            'error'     => $urlGenerator->isError() ? $translator->trans($urlGenerator->getError()) : false,
            'sourceUrl' => $urlGenerator->getSourceUrl(),
            'cartId'    => $cart->getId(),
        ];
    }

    /**
     * @Route("/sync/{id}", name="orocrm_magento_orderplace_new_cart_order_sync", requirements={"id"="\d+"}))
     * @AclAncestor("oro_workflow")
     */
    public function syncAction(Cart $cart)
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        try {
            $cartConnector  = $this->get('orocrm_magento.mage.cart_connector');
            $orderConnector = $this->get('orocrm_magento.mage.order_connector');
            $processor      = $this->get('oro_integration.sync.processor');

            $processor->process(
                $cart->getChannel(),
                $cartConnector->getType(),
                ['filters' => ['entity_id' => $cart->getOriginId()]]
            );
            $processor->process(
                $cart->getChannel(),
                $orderConnector->getType(),
                ['filters' => ['quote_id' => $cart->getOriginId()]]
            );

            $order = $em->getRepository('OroCRMMagentoBundle:Order')->getLastPlacedOrderBy($cart, 'cart');

            if (null === $order) {
                throw new \LogicException('Unable to load order.');
            }

            $redirectUrl = $this->generateUrl('orocrm_magento_order_view', ['id' => $order->getId()]);
            $message = $this->get('translator')->trans('orocrm.magento.controller.synchronization_success');
            $status = self::SYNC_SUCCESS;
        } catch (\Exception $e) {
            $cart->setStatusMessage('orocrm.magento.controller.synchronization_failed_status');

            // in import process we have EntityManager#clear()
            $cart = $em->merge($cart);
            $em->flush();
            $redirectUrl = $this->generateUrl('orocrm_magento_cart_view', ['id' => $cart->getId()]);
            $message = $this->get('translator')->trans('orocrm.magento.controller.sync_error_with_magento');
            $status = self::SYNC_ERROR;
        }

        return new JsonResponse(
            [
                'statusType' => $status,
                'message' => $message,
                'url' => $redirectUrl
            ]
        );
    }

    /**
     * @Route("/customer/{id}", name="orocrm_magento_widget_customer_orderplace", requirements={"id"="\d+"}))
     * @AclAncestor("oro_workflow")
     * @Template("OroCRMMagentoBundle:OrderPlace:place.html.twig")
     */
    public function customerAction(Customer $customer)
    {
        $urlGenerator = $this
            ->get('orocrm_magento.service.magento_url_generator')
            ->setChannel($customer->getChannel())
            ->setFlowName('oro_sales_new_order')
            ->setOrigin('customer')
            ->generate(
                $customer->getOriginId(),
                'orocrm_magento_orderplace_success',
                'orocrm_magento_orderplace_error'
            );

        $translator = $this->get('translator');

        return [
            'error'       => $urlGenerator->isError() ? $translator->trans($urlGenerator->getError()) : false,
            'sourceUrl'   => $urlGenerator->getSourceUrl(),
            'customerId'  => $customer->getid(),
        ];
    }

    /**
     * @Route(
     *   "/customer_sync/{id}",
     *   name="orocrm_magento_orderplace_new_customer_order_sync", requirements={"id"="\d+"})
     * )
     * @AclAncestor("oro_workflow")
     */
    public function customerSyncAction(Customer $customer)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        try {
            $orderConnector = $this->get('orocrm_magento.mage.order_connector');
            $processor      = $this->get('oro_integration.sync.processor');
            $processor->process(
                $customer->getChannel(),
                $orderConnector->getType(),
                ['filters' => ['customer_id' => $customer->getOriginId()]]
            );
            $order = $em->getRepository('OroCRMMagentoBundle:Order')->getLastPlacedOrderBy($customer, 'customer');
            if (null === $order) {
                throw new \LogicException('Unable to load order.');
            }
            $redirectUrl = $this->generateUrl('orocrm_magento_order_view', ['id' => $order->getId()]);
            $message = $this->get('translator')->trans('orocrm.magento.controller.synchronization_success');
            $status = self::SYNC_SUCCESS;
        } catch (\Exception $e) {
            $redirectUrl = $this->generateUrl('orocrm_magento_customer_view', ['id' => $customer->getId()]);
            $message = $this->get('translator')->trans('orocrm.magento.controller.sync_error_with_magento');
            $status = self::SYNC_ERROR;
        }
        return new JsonResponse(
            [
                'statusType' => $status,
                'message' => $message,
                'url' => $redirectUrl
            ]
        );
    }

    /**
     * @Route("/success", name="orocrm_magento_orderplace_success")
     * @AclAncestor("oro_workflow")
     * @Template
     */
    public function successAction()
    {
        return [];
    }

    /**
     * @Route("/error", name="orocrm_magento_orderplace_error"))
     * @AclAncestor("oro_workflow")
     * @Template
     */
    public function errorAction()
    {
        return [];
    }

    /**
     * Adds message to flash bag
     *
     * @param string $message
     * @param string $type
     */
    protected function addMessage($message, $type = 'success')
    {
        $this->get('session')->getFlashBag()->add($type, $this->get('translator')->trans($message));
    }
}
