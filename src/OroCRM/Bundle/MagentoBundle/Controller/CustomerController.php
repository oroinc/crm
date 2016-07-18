<?php

namespace OroCRM\Bundle\MagentoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

/**
 * @Route("/customer")
 */
class CustomerController extends Controller
{
    /**
     * @Route("/", name="orocrm_magento_customer_index")
     * @AclAncestor("orocrm_magento_customer_view")
     * @Template
     */
    public function indexAction()
    {
        return [
            'entity_class' => $this->container->getParameter('orocrm_magento.customer.entity.class')
        ];
    }

    /**
     * @param Customer $customer
     * @return array
     *
     * @Route("/view/{id}", name="orocrm_magento_customer_view", requirements={"id"="\d+"}))
     * @Acl(
     *      id="orocrm_magento_customer_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMMagentoBundle:Customer"
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
     * @Route("/update/{id}", name="orocrm_magento_customer_update", requirements={"id"="\d+"}))
     * @Acl(
     *      id="orocrm_magento_customer_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMMagentoBundle:Customer"
     * )
     * @Template("OroCRMMagentoBundle:Customer:update.html.twig")
     */
    public function updateAction(Customer $customer)
    {
        return $this->update($customer);
    }

    /**
     * @Route("/create", name="orocrm_magento_customer_create"))
     * @AclAncestor("oro_integration_assign")
     * @Acl(
     *      id="orocrm_magento_customer_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMMagentoBundle:Customer"
     * )
     * @Template("OroCRMMagentoBundle:Customer:update.html.twig")
     */
    public function createAction()
    {
        return $this->update(new Customer());
    }

    /**
     * @param Customer $customer
     * @return JsonResponse
     *
     * @Route("/register/{id}", name="orocrm_magento_customer_register", requirements={"id"="\d+"}))
     * @AclAncestor("orocrm_magento_customer_update")
     */
    public function registerAction(Customer $customer)
    {
        return new JsonResponse([
            'successful' => $this->get('orocrm_magento.form.handler.customer')->handleRegister($customer),
        ]);
    }

    /**
     * @param Customer $customer
     * @return array
     */
    protected function update(Customer $customer)
    {
        return $this->get('orocrm_magento.form.handler.customer')->handleUpdate(
            $customer,
            $this->createForm('orocrm_magento_customer', $customer),
            function (Customer $customer) {
                return [
                    'route' => 'orocrm_magento_customer_update',
                    'parameters' => ['id' => $customer->getId()]
                ];
            },
            function (Customer $customer) {
                return [
                    'route' => 'orocrm_magento_customer_view',
                    'parameters' => ['id' => $customer->getId()]
                ];
            },
            $this->get('translator')->trans('orocrm.magento.customer.saved.message')
        );
    }

    /**
     * @param Customer $customer
     * @return array
     *
     * @Route("/info/{id}", name="orocrm_magento_customer_info", requirements={"id"="\d+"}))
     * @AclAncestor("orocrm_magento_customer_view")
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
     *          name="orocrm_magento_widget_account_customers_info",
     *          requirements={"accountId"="\d+", "channelId"="\d+"}
     * )
     * @ParamConverter("account", class="OroCRMAccountBundle:Account", options={"id" = "accountId"})
     * @ParamConverter("channel", class="OroCRMChannelBundle:Channel", options={"id" = "channelId"})
     * @AclAncestor("orocrm_magento_customer_view")
     * @Template
     */
    public function accountCustomersInfoAction(Account $account, Channel $channel)
    {
        $customers = $this->getDoctrine()
            ->getRepository('OroCRM\\Bundle\\MagentoBundle\\Entity\\Customer')
            ->findBy(['account' => $account, 'dataChannel' => $channel]);
        $customers = array_filter(
            $customers,
            function ($item) {
                return $this->get('oro_security.security_facade')->isGranted('VIEW', $item);
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
     *        name="orocrm_magento_widget_customer_info",
     *        requirements={"id"="\d+", "channelId"="\d+"}
     * )
     * @ParamConverter("channel", class="OroCRMChannelBundle:Channel", options={"id" = "channelId"})
     * @AclAncestor("orocrm_magento_customer_view")
     * @Template
     */
    public function customerInfoAction(Customer $customer, Channel $channel)
    {
        return [
            'customer'       => $customer,
            'channel'        => $channel,
            'orderClassName' => $this->container->getParameter('orocrm_magento.entity.order.class'),
            'cartClassName'  => $this->container->getParameter('orocrm_magento.entity.cart.class'),
        ];
    }

    /**
     * @param Customer $customer
     * @return array
     *
     * @Route("/order/{id}", name="orocrm_magento_customer_orderplace", requirements={"id"="\d+"}))
     * @AclAncestor("orocrm_magento_customer_view")
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
     * @Route("/addressBook/{id}", name="orocrm_magento_customer_address_book", requirements={"id"="\d+"}))
     * @AclAncestor("orocrm_magento_customer_view")
     * @Template
     */
    public function addressBookAction(Customer $customer)
    {
        return ['entity' => $customer];
    }
}
