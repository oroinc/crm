<?php

namespace OroCRM\Bundle\MagentoBundle\Controller;

use Doctrine\ORM\EntityManager;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\ImportExportBundle\Writer\EntityWriter;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

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
     * @Template("OroCRMMagentoBundle:OrderPlace:widget/place.html.twig")
     * @param Cart $cart
     * @return array
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

            $order = $em->getRepository('OroCRMMagentoBundle:Order')->getLastPlacedOrderBy($cart, 'cart');
            if (null === $order) {
                throw new \LogicException('Unable to load order.');
            }

            $redirectUrl = $this->generateUrl('orocrm_magento_order_view', ['id' => $order->getId()]);
            $message = $this->get('translator')->trans('orocrm.magento.controller.synchronization_success');
            $status = self::SYNC_SUCCESS;
        } catch (\Exception $e) {
            $cart->setStatusMessage('orocrm.magento.controller.synchronization_failed_status');
            $em->flush($cart);
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
     * @Template("OroCRMMagentoBundle:OrderPlace:widget/place.html.twig")
     * @param Customer $customer
     * @return array
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
            'customerId'  => $customer->getId(),
        ];
    }

    /**
     * @Route(
     *   "/customer_sync/{id}",
     *   name="orocrm_magento_orderplace_new_customer_order_sync", requirements={"id"="\d+"})
     * )
     * @AclAncestor("oro_workflow")
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

    /**
     * @param Channel $channel
     * @param array $configuration
     * @return bool
     */
    protected function loadOrderInformation(Channel $channel, array $configuration = [])
    {
        $orderInformationLoader = $this->get('orocrm_magento.service.order.information_loader');

        return $orderInformationLoader->load($channel, $configuration);
    }

    /**
     * @param Channel $channel
     * @param array $configuration
     * @return bool
     */
    protected function loadCartInformation(Channel $channel, array $configuration = [])
    {
        $cartInformationLoader = $this->get('orocrm_magento.service.cart.information_loader');

        return $cartInformationLoader->load($channel, $configuration);
    }
}
