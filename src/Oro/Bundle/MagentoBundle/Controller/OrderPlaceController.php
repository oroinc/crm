<?php

namespace Oro\Bundle\MagentoBundle\Controller;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\ImportExportBundle\Writer\EntityWriter;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\SecurityBundle\Annotation\CsrfProtection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Magento Order Place Controller
 * @Route("/order/place")
 */
class OrderPlaceController extends Controller
{
    const SYNC_SUCCESS    = 'success';
    const SYNC_ERROR      = 'error';

    /**
     * @Route("/cart/{id}", name="oro_magento_orderplace_cart", requirements={"id"="\d+"}))
     * @Template("OroMagentoBundle:OrderPlace:widget/place.html.twig")
     * @param Cart $cart
     * @return array
     */
    public function cartAction(Cart $cart)
    {
        $urlGenerator = $this
            ->get('oro_magento.service.magento_url_generator')
            ->setChannel($cart->getChannel())
            ->setFlowName('oro_sales_new_order')
            ->setOrigin('quote')
            ->generate(
                $cart->getOriginId(),
                'oro_magento_orderplace_success',
                'oro_magento_orderplace_error'
            );

        $translator = $this->get('translator');

        return [
            'error'     => $urlGenerator->isError() ? $translator->trans($urlGenerator->getError()) : false,
            'sourceUrl' => $urlGenerator->getSourceUrl(),
            'cartId'    => $cart->getId(),
        ];
    }

    /**
     * @Route(
     *     "/sync/{id}",
     *     name="oro_magento_orderplace_new_cart_order_sync",
     *     requirements={"id"="\d+"},
     *     methods={"POST"}
     * )
     * @CsrfProtection()
     *
     * @param Cart $cart
     * @return JsonResponse
     */
    public function syncAction(Cart $cart)
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        try {
            $isOrderLoaded = $this->loadOrderInformation(
                $cart->getChannel(),
                [
                    'filters' => ['quote_id' => $cart->getOriginId()],
                    ProcessorRegistry::TYPE_IMPORT => [EntityWriter::SKIP_CLEAR => true]
                ]
            );

            $isCartLoaded = $this->loadCartInformation(
                $cart->getChannel(),
                [
                    'filters' => ['entity_id' => $cart->getOriginId()],
                    ProcessorRegistry::TYPE_IMPORT => [EntityWriter::SKIP_CLEAR => true]
                ]
            );

            if (!$isOrderLoaded || !$isCartLoaded) {
                throw new \LogicException('Unable to load information.');
            }

            $order = $em->getRepository('OroMagentoBundle:Order')->getLastPlacedOrderBy($cart, 'cart');
            if (null === $order) {
                throw new \LogicException('Unable to load order.');
            }

            $redirectUrl = $this->generateUrl('oro_magento_order_view', ['id' => $order->getId()]);
            $message = $this->get('translator')->trans('oro.magento.controller.synchronization_success');
            $status = self::SYNC_SUCCESS;
        } catch (\Exception $e) {
            $cart->setStatusMessage('oro.magento.controller.synchronization_failed_status');
            $em->flush($cart);
            $redirectUrl = $this->generateUrl('oro_magento_cart_view', ['id' => $cart->getId()]);
            $message = $this->get('translator')->trans('oro.magento.controller.sync_error_with_magento');
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
     * @Route("/customer/{id}", name="oro_magento_widget_customer_orderplace", requirements={"id"="\d+"}))
     * @Template("OroMagentoBundle:OrderPlace:widget/place.html.twig")
     * @param Customer $customer
     * @return array
     */
    public function customerAction(Customer $customer)
    {
        $urlGenerator = $this
            ->get('oro_magento.service.magento_url_generator')
            ->setChannel($customer->getChannel())
            ->setFlowName('oro_sales_new_order')
            ->setOrigin('customer')
            ->generate(
                $customer->getOriginId(),
                'oro_magento_orderplace_success',
                'oro_magento_orderplace_error'
            );

        $translator = $this->get('translator');

        return [
            'error'       => $urlGenerator->isError() ? $translator->trans($urlGenerator->getError()) : false,
            'sourceUrl'   => $urlGenerator->getSourceUrl(),
            'customerId'  => $customer->getId(),
        ];
    }

    /**
     * @Route(
     *     "/customer_sync/{id}",
     *     name="oro_magento_orderplace_new_customer_order_sync",
     *     requirements={"id"="\d+"},
     *     methods={"POST"}
     * )
     * @CsrfProtection()
     *
     * @param Customer $customer
     * @return JsonResponse
     */
    public function customerSyncAction(Customer $customer)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        try {
            $isOrderLoaded = $this->loadOrderInformation(
                $customer->getChannel(),
                ['filters' => ['customer_id' => $customer->getOriginId()]]
            );
            if (!$isOrderLoaded) {
                throw new \LogicException('Unable to load order.');
            }
            $order = $em->getRepository('OroMagentoBundle:Order')->getLastPlacedOrderBy($customer, 'customer');
            if (null === $order) {
                throw new \LogicException('Unable to load order.');
            }

            $redirectUrl = $this->generateUrl('oro_magento_order_view', ['id' => $order->getId()]);
            $message = $this->get('translator')->trans('oro.magento.controller.synchronization_success');
            $status = self::SYNC_SUCCESS;
        } catch (\Exception $e) {
            $redirectUrl = $this->generateUrl('oro_magento_customer_view', ['id' => $customer->getId()]);
            $message = $this->get('translator')->trans('oro.magento.controller.sync_error_with_magento');
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
     * @Route("/success", name="oro_magento_orderplace_success")
     * @Template
     */
    public function successAction()
    {
        return [];
    }

    /**
     * @Route("/error", name="oro_magento_orderplace_error"))
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

    /**
     * @param Channel $channel
     * @param array $configuration
     * @return bool
     */
    protected function loadCartInformation(Channel $channel, array $configuration = [])
    {
        $cartInformationLoader = $this->get('oro_magento.service.cart.information_loader');

        return $cartInformationLoader->load($channel, $configuration);
    }
}
