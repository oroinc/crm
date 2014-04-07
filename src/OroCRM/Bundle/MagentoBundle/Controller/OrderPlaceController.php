<?php

namespace OroCRM\Bundle\MagentoBundle\Controller;

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
        $channel = $cart->getChannel();
        $error   = $sourceUrl = false;
        try {
            $url = $channel->getTransport()->getAdminUrl();
            if (false === $url) {
                throw new ExtensionRequiredException();
            }

            $successUrl = urlencode(
                $this->generateUrl(
                    'orocrm_magento_orderplace_cart_success',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            );

            $errorUrl = urlencode(
                $this->generateUrl(
                    'orocrm_magento_orderplace_external_error',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            );

            $sourceUrl = sprintf(
                '%sdasd/%s?quote=%d&route=%s&workflow=%s&success_url=%s&error_url=%s',
                rtrim($url, '/'),
                self::GATEWAY_ROUTE,
                $cart->getOriginId(),
                self::NEW_ORDER_ROUTE,
                self::FLOW_NAME,
                $successUrl,
                $errorUrl
            );
            try {
                $httpStatus = StaticClient::get($sourceUrl)->getStatusCode();
                if ($httpStatus >= 400 && false !== $httpStatus) {
                    throw new \LogicException('Unable to load resource');
                }
            } catch (\Exception $e) {
                $error = 'orocrm.magento.ping_site_error';
            }
        } catch (ExtensionRequiredException $e) {
            $error = $e->getMessage();
        } catch (\LogicException $e) {
            $error = 'orocrm.magento.controller.transport_not_configure';
        }

        return [
            'entity'    => $cart,
            'error'     => $error ? $this->get('translator')->trans($error) : $error,
            'sourceUrl' => $sourceUrl,
            'backUrl'   => $this->generateUrl('orocrm_magento_cart_view', ['id' => $cart->getId()])
        ];
    }

    /**
     * @Route("/sync/{id}", name="orocrm_magento_orderplace_sync", requirements={"id"="\d+"}))
     * @AclAncestor("oro_workflow")
     */
    public function syncAction(Cart $cart)
    {
        $status = 200;

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
        } catch (\Exception $e) {
            $cart->setStatusMessage(
                $this->get('translator')->trans('orocrm.magento.controller.synchronization_failed_status')
            );
            $em = $this->get('doctrine')->getManagerForClass('OroCRMMagentoBundle:Cart');
            $em->persist($cart);
            $em->flush();
            $status = 400;
        }

        return new Response('', $status);
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
}
