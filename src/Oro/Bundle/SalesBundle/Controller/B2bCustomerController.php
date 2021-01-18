<?php

namespace Oro\Bundle\SalesBundle\Controller;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Form\Type\B2bCustomerType;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The controller for B2bCustomer entity.
 * @Route("/b2bcustomer")
 */
class B2bCustomerController extends AbstractController
{
    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_sales_b2bcustomer_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format"="html"}
     * )
     * @Template
     * @AclAncestor("oro_sales_b2bcustomer_view")
     */
    public function indexAction()
    {
        return [
            'entity_class' => B2bCustomer::class
        ];
    }

    /**
     * @Route("/view/{id}", name="oro_sales_b2bcustomer_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_sales_b2bcustomer_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroSalesBundle:B2bCustomer"
     * )
     */
    public function viewAction(B2bCustomer $customer)
    {
        return [
            'entity' => $customer
        ];
    }

    /**
     * @Route("/widget/info/{id}", name="oro_sales_b2bcustomer_widget_info", requirements={"id"="\d+"})
     * @AclAncestor("oro_sales_b2bcustomer_view")
     * @Template
     */
    public function infoAction(B2bCustomer $customer)
    {
        return [
            'entity' => $customer
        ];
    }

    /**
     * @Route("/widget/b2bcustomer-leads/{id}", name="oro_sales_b2bcustomer_widget_leads", requirements={"id"="\d+"})
     * @AclAncestor("oro_sales_lead_view")
     * @Template
     */
    public function b2bCustomerLeadsAction(B2bCustomer $customer)
    {
        return [
            'entity' => $customer
        ];
    }

    /**
     * Create b2bcustomer form
     *
     * @Route("/create", name="oro_sales_b2bcustomer_create")
     * @Acl(
     *      id="oro_sales_b2bcustomer_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroSalesBundle:B2bCustomer"
     * )
     * @Template("OroSalesBundle:B2bCustomer:update.html.twig")
     */
    public function createAction()
    {
        return $this->update(new B2bCustomer());
    }

    /**
     * @param  B2bCustomer $entity
     *
     * @return array
     */
    protected function update(B2bCustomer $entity = null)
    {
        return $this->get('oro_form.model.update_handler')->handleUpdate(
            $entity,
            $this->createForm(B2bCustomerType::class, $entity),
            function (B2bCustomer $entity) {
                return [
                    'route' => 'oro_sales_b2bcustomer_update',
                    'parameters' => ['id' => $entity->getId()],
                ];
            },
            function (B2bCustomer $entity) {
                return [
                    'route' => 'oro_sales_b2bcustomer_view',
                    'parameters' => ['id' => $entity->getId()],
                ];
            },
            $this->get('translator')->trans('oro.sales.controller.b2bcustomer.saved.message'),
            $this->get('oro_sales.b2bcustomer.form.handler')
        );
    }

    /**
     * Update user form
     * @Route("/update/{id}", name="oro_sales_b2bcustomer_update", requirements={"id"="\d+"}, defaults={"id"=0})
     *
     * @Template
     * @Acl(
     *      id="oro_sales_b2bcustomer_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroSalesBundle:B2bCustomer"
     * )
     */
    public function updateAction(B2bCustomer $entity)
    {
        return $this->update($entity);
    }

    /**
     * @Route(
     *      "/widget/b2bcustomer-opportunities/{id}",
     *      name="oro_sales_b2bcustomer_widget_opportunities",
     *      requirements={"id"="\d+"}
     * )
     * @AclAncestor("oro_sales_opportunity_view")
     * @Template
     */
    public function b2bCustomerOpportunitiesAction(B2bCustomer $customer)
    {
        return [
            'entity' => $customer
        ];
    }

    /**
     * @Route(
     *      "/widget/b2bcustomers-info/account/{accountId}/channel/{channelId}",
     *      name="oro_sales_widget_account_b2bcustomers_info",
     *      requirements={"accountId"="\d+", "channelId"="\d+"}
     * )
     * @ParamConverter("account", class="OroAccountBundle:Account", options={"id" = "accountId"})
     * @ParamConverter("channel", class="OroChannelBundle:Channel", options={"id" = "channelId"})
     * @AclAncestor("oro_sales_b2bcustomer_view")
     * @Template
     */
    public function accountCustomersInfoAction(Account $account, Channel $channel)
    {
        $customers = $this->getDoctrine()
            ->getRepository('OroSalesBundle:B2bCustomer')
            ->findBy(['account' => $account, 'dataChannel' => $channel]);

        return ['account' => $account, 'customers' => $customers, 'channel' => $channel];
    }

    /**
     * @Route(
     *        "/widget/b2bcustomer-info/{id}/channel/{channelId}",
     *        name="oro_sales_widget_b2bcustomer_info",
     *        requirements={"id"="\d+", "channelId"="\d+"}
     * )
     * @ParamConverter("channel", class="OroChannelBundle:Channel", options={"id" = "channelId"})
     * @AclAncestor("oro_sales_b2bcustomer_view")
     * @Template
     */
    public function customerInfoAction(B2bCustomer $customer, Channel $channel)
    {
        return [
            'customer'             => $customer,
            'channel'              => $channel,
            'leadClassName'        => Lead::class,
            'opportunityClassName' => Opportunity::class
        ];
    }
}
