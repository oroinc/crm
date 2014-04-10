<?php

namespace OroCRM\Bundle\MagentoBundle\Controller;

use Doctrine\ORM\EntityManager;

use Guzzle\Http\StaticClient;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\IntegrationBundle\Model\IntegrationEntityTrait;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Exception\ExtensionRequiredException;

/**
 * @Route("/order/place")
 */
class OrderPlaceController extends Controller
{
    const FLOW_NAME       = 'oro_sales_new_order';
    const GATEWAY_ROUTE   = 'oro_gateway/do';
    const NEW_ORDER_ROUTE = 'oro_sales/newOrder';

    /**
     * @Route("/cart/{id}", name="orocrm_magento_orderplace_cart", requirements={"id"="\d+"}))
     * @AclAncestor("oro_workflow")
     * @Template("OroCRMMagentoBundle:OrderPlace:place.html.twig")
     */
    public function cartAction(Cart $cart)
    {
        $UrlGenerator = $this
            ->get('orocrm_magento.service.magento_url_generator')
            ->setChannel($cart->getChannel())
            ->setFlowName('oro_sales_new_order')
            ->setOrigin('quote')
        ;

        $UrlGenerator->setSourceUrl(
            $cart->getOriginId(),
            'orocrm_magento_orderplace_cart_success',
            'orocrm_magento_orderplace_external_error'
        );

        return [
            'error'     => $UrlGenerator->isError()
                                ? $this->get('translator')->trans($UrlGenerator->getError())
                                : $UrlGenerator->getError(),
            'sourceUrl' => $UrlGenerator->getSourceUrl()
        ];
    }

    /**
     * @Route("/sync/{id}", name="orocrm_magento_orderplace_sync", requirements={"id"="\d+"}))
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

            $order = $em->getRepository('OroCRMMagentoBundle:Order')->getLastPlacedOrderByCart($cart);

            if (null === $order) {
                throw new \LogicException('Unable to load order.');
            }

            $redirectUrl = $this->generateUrl('orocrm_magento_order_view', ['id' => $order->getId()]);
            $this->addMessage('orocrm.magento.controller.synchronization_success');
        } catch (\Exception $e) {
            $cart->setStatusMessage('orocrm.magento.controller.synchronization_failed_status');

            // in import process we have EntityManager#clear()
            $cart = $em->merge($cart);
            $em->flush();
            $redirectUrl = $this->generateUrl('orocrm_magento_cart_view', ['id' => $cart->getId()]);
            $this->addMessage('orocrm.magento.controller.synchronization_error', 'error');
        }

        return $this->redirect($redirectUrl);
    }

    /**
     * @Route("/customer/{id}", name="orocrm_magento_orderplace_customer", requirements={"id"="\d+"}))
     * @AclAncestor("oro_workflow")
     * @Template("OroCRMMagentoBundle:OrderPlace:place.html.twig")
     */
    public function customerAction(Customer $customer)
    {
        $UrlGenerator = $this
            ->get('orocrm_magento.service.magento_url_generator')
            ->setChannel($customer->getChannel())
            ->setFlowName('oro_sales_new_order')
            ->setOrigin('customer')
        ;

        $UrlGenerator->setSourceUrl(
            $customer->getOriginId(),
            'orocrm_magento_orderplace_customer_success',
            'orocrm_magento_orderplace_external_error'
        );

        return [
            'error'     => $UrlGenerator->isError()
                                ? $this->get('translator')->trans($UrlGenerator->getError())
                                : $UrlGenerator->getError(),
            'sourceUrl' => $UrlGenerator->getSourceUrl()
        ];
    }

    /**
     * @Route("/unisync/{id}", name="orocrm_magento_orderplace_customer_sync", requirements={"id"="\d+"}))
     * @AclAncestor("oro_workflow")
     */
    public function customerSyncAction(Customer $customer)
    {

        $itemSync = $this->get('orocrm_magento.service.magento_items_synchronizer');

        $itemSync->setItem($customer);

        $itemSync->setConnector($this->get('orocrm_magento.mage.customer_connector'));

        $itemSync->sync('customer', 'orocrm_magento_customer_view');

        var_dump($itemSync);

        /*
        $em = $this->get('doctrine.orm.entity_manager');

        try {
            $itemConnector  = $this->get('orocrm_magento.mage.customer_connector');
            $orderConnector = $this->get('orocrm_magento.mage.order_connector');
            $processor      = $this->get('oro_integration.sync.processor');

            $processor->process(
                $customer->getChannel(),
                $itemConnector->getType(),
                ['filters' => ['entity_id' => $customer->getOriginId()]]
            );
            $processor->process(
                $customer->getChannel(),
                $orderConnector->getType(),
                ['filters' => ['quote_id' => $customer->getOriginId()]]
            );

            $order = $em->getRepository('OroCRMMagentoBundle:Order')->getLastPlacedOrderBy($customer, 'customer');

            if (null === $order) {
                throw new \LogicException('Unable to load order.');
            }

            $redirectUrl = $this->generateUrl('orocrm_magento_order_view', ['id' => $order->getId()]);
            $this->addMessage('orocrm.magento.controller.synchronization_success');
        } catch (\Exception $e) {
            #$customer->setStatusMessage('orocrm.magento.controller.synchronization_failed_status');

            // in import process we have EntityManager#clear()
            $customer = $em->merge($customer);
            $em->flush();
            $redirectUrl = $this->generateUrl('orocrm_magento_customer_view', ['id' => $customer->getId()]);
            $this->addMessage('orocrm.magento.controller.synchronization_error', 'error');
        }*/
        return [];
        #return $this->redirect($redirectUrl);
    }




    /**
     * @Route("/cart/success", name="orocrm_magento_orderplace_cart_success"))
     * @AclAncestor("oro_workflow")
     * @Template
     */
    public function cartSuccessAction()
    {
        return [];
    }

    /**
     * @Route("/customer/success", name="orocrm_magento_orderplace_customer_success"))
     * @AclAncestor("oro_workflow")
     * @Template
     */
    public function customerSuccessAction()
    {
        return [];
    }

    /**
     * @Route("/error", name="orocrm_magento_orderplace_external_error"))
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
