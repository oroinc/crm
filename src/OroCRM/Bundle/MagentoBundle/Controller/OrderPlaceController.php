<?php

namespace OroCRM\Bundle\MagentoBundle\Controller;

use Doctrine\ORM\EntityManager;

use Guzzle\Http\StaticClient;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\JsonResponse;
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
        $channel      = $cart->getChannel();
        $error        = $sourceUrl = $httpStatus = false;
        $successRoute = 'orocrm_magento_orderplace_cart_success';
        $errorRoute   = 'orocrm_magento_orderplace_external_error';

        try {
            $url = $channel->getTransport()->getAdminUrl();
            if (false === $url) {
                throw new ExtensionRequiredException();
            }

            $successUrl = urlencode($this->generateUrl($successRoute, [], UrlGeneratorInterface::ABSOLUTE_URL));
            $errorUrl   = urlencode($this->generateUrl($errorRoute, [], UrlGeneratorInterface::ABSOLUTE_URL));

            $sourceUrl = sprintf(
                '%s/%s?quote=%d&route=%s&workflow=%s&success_url=%s&error_url=%s',
                rtrim($url, '/'),
                self::GATEWAY_ROUTE,
                $cart->getOriginId(),
                self::NEW_ORDER_ROUTE,
                self::FLOW_NAME,
                $successUrl,
                $errorUrl
            );

            // ping url just to ensure that it's accessible
            $httpStatus = StaticClient::get($sourceUrl)->getStatusCode();
            if (false !== $httpStatus && $httpStatus >= 400) {
                throw new \LogicException('Unable to load resource');
            }
        } catch (ExtensionRequiredException $e) {
            $error = $e->getMessage();
        } catch (\LogicException $e) {
            $error = 'orocrm.magento.controller.transport_not_configure';
        }

        return [
            'error'     => $error ? $this->get('translator')->trans($error) : $error,
            'sourceUrl' => $sourceUrl,
            'cartId'    => $cart->getId(),
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
            $message = $this->get('translator')->trans('orocrm.magento.controller.synchronization_success');
            $status = 'success';
        } catch (\Exception $e) {
            $cart->setStatusMessage('orocrm.magento.controller.synchronization_failed_status');

            // in import process we have EntityManager#clear()
            $cart = $em->merge($cart);
            $em->flush();
            $redirectUrl = $this->generateUrl('orocrm_magento_cart_view', ['id' => $cart->getId()]);
            $message = $this->get('translator')->trans('orocrm.magento.controller.synchronization_error');
            $status = 'error';
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
     * @Route("/customer/{id}", name="orocrm_magento_orderplace_customer", requirements={"id"="\d+"}))
     * @AclAncestor("oro_workflow")
     * @Template("OroCRMMagentoBundle:OrderPlace:place.html.twig")
     */
    public function customerAction(Customer $customer)
    {
        // @TODO order creation from customer page
        return [];
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
