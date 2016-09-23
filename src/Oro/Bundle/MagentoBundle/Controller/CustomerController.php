<?php

namespace Oro\Bundle\MagentoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;

/**
 * @Route("/customer")
 */
class CustomerController extends Controller
{
    /**
     * @Route("/", name="oro_magento_customer_index")
     * @AclAncestor("oro_magento_customer_view")
     * @Template
     */
    public function indexAction()
    {
        return [
            'entity_class' => $this->container->getParameter('oro_magento.customer.entity.class')
        ];
    }

    /**
     * @param Customer $customer
     * @return array
     *
     * @Route("/view/{id}", name="oro_magento_customer_view", requirements={"id"="\d+"}))
     * @Acl(
     *      id="oro_magento_customer_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroMagentoBundle:Customer"
     * )
     * @Template
     */
    public function viewAction(Customer $customer)
    {
        return ['entity' => $customer];
    }

    /**
     * @param Customer $customer
     * @return array
     *
     * @Route("/update/{id}", name="oro_magento_customer_update", requirements={"id"="\d+"}))
     * @Acl(
     *      id="oro_magento_customer_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroMagentoBundle:Customer"
     * )
     * @Template("OroMagentoBundle:Customer:update.html.twig")
     */
    public function updateAction(Customer $customer)
    {
        return $this->update($customer);
    }

    /**
     * @Route("/create", name="oro_magento_customer_create"))
     * @Acl(
     *      id="oro_magento_customer_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroMagentoBundle:Customer"
     * )
     * @Template("OroMagentoBundle:Customer:update.html.twig")
     */
    public function createAction()
    {
        if (!$this->getSecurityFacade()->isGranted('oro_integration_assign')) {
            throw new AccessDeniedHttpException();
        }

        return $this->update(new Customer());
    }

    /**
     * @param Customer $customer
     * @return JsonResponse
     *
     * @Route("/register/{id}", name="oro_magento_customer_register", requirements={"id"="\d+"}))
     * @AclAncestor("oro_magento_customer_update")
     */
    public function registerAction(Customer $customer)
    {
        return new JsonResponse([
            'successful' => $this->get('oro_magento.form.handler.customer')->handleRegister($customer),
        ]);
    }

    /**
     * @param Customer $customer
     * @return array
     */
    protected function update(Customer $customer)
    {
        return $this->get('oro_magento.form.handler.customer')->handleUpdate(
            $customer,
            $this->createForm('oro_magento_customer', $customer),
            function (Customer $customer) {
                return [
                    'route' => 'oro_magento_customer_update',
                    'parameters' => ['id' => $customer->getId()]
                ];
            },
            function (Customer $customer) {
                return [
                    'route' => 'oro_magento_customer_view',
                    'parameters' => ['id' => $customer->getId()]
                ];
            },
            $this->get('translator')->trans('oro.magento.customer.saved.message')
        );
    }

    /**
     * @param Customer $customer
     * @return array
     *
     * @Route("/info/{id}", name="oro_magento_customer_info", requirements={"id"="\d+"}))
     * @AclAncestor("oro_magento_customer_view")
     * @Template
     */
    public function infoAction(Customer $customer)
    {
        return ['entity' => $customer];
    }

    /**
     * @param Account $account
     * @param Channel $channel
     * @return array
     *
     * @Route(
     *         "/widget/customers-info/{accountId}/{channelId}",
     *          name="oro_magento_widget_account_customers_info",
     *          requirements={"accountId"="\d+", "channelId"="\d+"}
     * )
     * @ParamConverter("account", class="OroAccountBundle:Account", options={"id" = "accountId"})
     * @ParamConverter("channel", class="OroChannelBundle:Channel", options={"id" = "channelId"})
     * @AclAncestor("oro_magento_customer_view")
     * @Template
     */
    public function accountCustomersInfoAction(Account $account, Channel $channel)
    {
        $customers = $this->getDoctrine()
            ->getRepository('Oro\\Bundle\\MagentoBundle\\Entity\\Customer')
            ->findBy(['account' => $account, 'dataChannel' => $channel]);
        $customers = array_filter(
            $customers,
            function ($item) {
                return $this->getSecurityFacade()->isGranted('VIEW', $item);
            }
        );

        return ['customers' => $customers, 'channel' => $channel, 'account' => $account];
    }

    /**
     * @param Customer $customer
     * @param Channel $channel
     * @return array
     *
     * @Route(
     *        "/widget/customer-info/{id}/{channelId}",
     *        name="oro_magento_widget_customer_info",
     *        requirements={"id"="\d+", "channelId"="\d+"}
     * )
     * @ParamConverter("channel", class="OroChannelBundle:Channel", options={"id" = "channelId"})
     * @AclAncestor("oro_magento_customer_view")
     * @Template
     */
    public function customerInfoAction(Customer $customer, Channel $channel)
    {
        return [
            'customer'       => $customer,
            'channel'        => $channel,
            'orderClassName' => $this->container->getParameter('oro_magento.entity.order.class'),
            'cartClassName'  => $this->container->getParameter('oro_magento.entity.cart.class'),
        ];
    }

    /**
     * @param Request $request
     * @return array
     *
     * @Route(
     *        "/widget/tracking-events",
     *        name="oro_magento_widget_tracking_events"
     * )
     * @AclAncestor("oro_magento_customer_view")
     * @Template
     */
    public function trackingEventsAction(Request $request)
    {
        $customerIds = $request->query->filter(
            'customerIds',
            [],
            false,
            FILTER_VALIDATE_INT,
            FILTER_REQUIRE_ARRAY
        );

        return ['customerIds' => $customerIds];
    }

    /**
     * @param Customer $customer
     * @return array
     *
     * @Route("/order/{id}", name="oro_magento_customer_orderplace", requirements={"id"="\d+"}))
     * @AclAncestor("oro_magento_customer_view")
     * @Template
     */
    public function placeOrderAction(Customer $customer)
    {
        return ['entity' => $customer];
    }

    /**
     * @param Customer $customer
     * @return array
     *
     * @Route("/addressBook/{id}", name="oro_magento_customer_address_book", requirements={"id"="\d+"}))
     * @AclAncestor("oro_magento_customer_view")
     * @Template
     */
    public function addressBookAction(Customer $customer)
    {
        return ['entity' => $customer];
    }

    /**
     * @return SecurityFacade
     */
    protected function getSecurityFacade()
    {
        return $this->get('oro_security.security_facade');
    }
}
