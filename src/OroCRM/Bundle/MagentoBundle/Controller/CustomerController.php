<?php

namespace OroCRM\Bundle\MagentoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

/**
 * @Route("/customer")
 */
class CustomerController extends Controller
{
    /**
     * @Route("/{id}", name="orocrm_magento_customer_index", requirements={"id"="\d+"}))
     * @AclAncestor("orocrm_magento_customer_view")
     * @Template
     */
    public function indexAction(Channel $channel)
    {
        return ['channelId' => $channel->getId()];
    }

    /**
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
     * @Route("/info/{id}", name="orocrm_magento_customer_info", requirements={"id"="\d+"}))
     * @AclAncestor("orocrm_magento_customer_view")
     * @Template
     */
    public function infoAction(Customer $customer)
    {
        return ['entity' => $customer];
    }

    /**
     * @Route(
     *         "/widget/customers-info/{id}/{channelId}",
     *          name="orocrm_customers_info_widget",
     *          requirements={"id"="\d+", "channelId"="\d+"}
     * )
     * @Template
     */
    public function accountCustomersInfoAction($id, $channelId)
    {
        $customers = $this->getDoctrine()
            ->getRepository('\OroCRM\Bundle\MagentoBundle\Entity\Customer')
            ->findBy(array('account' => $id, 'channel' => $channelId));

        return array('customers' => $customers, 'channelId' => $channelId);
    }

    /**
     * @Route(
     *        "/widget/customer-info/{id}/{channelId}",
     *        name="orocrm_customer_info_widget",
     *        requirements={"id"="\d+", "channelId"="\d+"}
     * )
     * @Template
     */
    public function accountCustomerInfoAction(Customer $customer, $channelId)
    {
        return array('customer' => $customer, 'channelId' => $channelId);
    }
}
