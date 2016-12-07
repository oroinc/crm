<?php

namespace Oro\Bundle\SalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

/**
 * @Route("/opportunity")
 */
class OpportunityController extends Controller
{
    /**
     * @Route("/view/{id}", name="oro_sales_opportunity_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_sales_opportunity_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroSalesBundle:Opportunity"
     * )
     */
    public function viewAction(Opportunity $entity)
    {
        return array(
            'entity' => $entity,
        );
    }

    /**
     * @Route("/info/{id}", name="oro_sales_opportunity_info", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("oro_sales_opportunity_view")
     */
    public function infoAction(Opportunity $entity)
    {
        return array(
            'entity'  => $entity
        );
    }

    /**
     * @Route("/create", name="oro_sales_opportunity_create")
     * @Template("OroSalesBundle:Opportunity:update.html.twig")
     * @Acl(
     *      id="oro_sales_opportunity_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroSalesBundle:Opportunity"
     * )
     */
    public function createAction()
    {
        return $this->update(new Opportunity());
    }

    /**
     * @Route("/update/{id}", name="oro_sales_opportunity_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="oro_sales_opportunity_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroSalesBundle:Opportunity"
     * )
     */
    public function updateAction(Opportunity $entity)
    {
        return $this->update($entity);
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_sales_opportunity_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     * @AclAncestor("oro_sales_opportunity_view")
     */
    public function indexAction()
    {
        return [
            'entity_class' => $this->container->getParameter('oro_sales.opportunity.class')
        ];
    }

    /**
     * Create opportunity form with data channel
     *
     * @Route("/create/{channelIds}", name="oro_sales_opportunity_data_channel_aware_create")
     * @Template("OroSalesBundle:Opportunity:update.html.twig")
     * @AclAncestor("oro_sales_opportunity_create")
     *
     * @ParamConverter(
     *      "channel",
     *      class="OroChannelBundle:Channel",
     *      options={"id" = "channelIds"}
     * )
     */
    public function opportunityWithDataChannelCreateAction(Channel $channel)
    {
        $opportunity = new Opportunity();
        $opportunity->setDataChannel($channel);

        return $this->update($opportunity);
    }

    /**
     * Create opportunity form with customer association set
     *
     * @Route("/create/{targetClass}/{targetId}", name="oro_sales_opportunity_customer_aware_create")
     * @Template("OroSalesBundle:Opportunity:update.html.twig")
     * @AclAncestor("oro_sales_opportunity_create")
     *
     */
    public function opportunityWithCustomerCreateAction($targetClass, $targetId)
    {
        $target = $this->getEntityRoutingHelper()->getEntity($targetClass, $targetId);
        $customer = $this->getAccountCustomerManager()->getOrCreateAccountCustomerByTarget($target);

        $opportunity = new Opportunity();
        $opportunity->setCustomerAssociation($customer);

        return $this->update($opportunity);
    }

    /**
     * @Route(
     *     "/datagrid/opportunity-with-datachannel/{channelIds}",
     *     name="oro_sales_datagrid_opportunity_datachannel_aware"
     * )
     * @Template("OroSalesBundle:Widget:entityWithDataChannelGrid.html.twig")
     * @AclAncestor("oro_sales_opportunity_view")
     */
    public function opportunityWithDataChannelGridAction($channelIds, Request $request)
    {
        $gridName = $request->query->get('gridName');

        if (!$gridName) {
            return $this->createNotFoundException('`gridName` Should be defined.');
        }

        return [
            'channelId'    => $channelIds,
            'gridName'     => $gridName,
            'params'       => $request->query->get('params', []),
            'renderParams' => $request->query->get('renderParams', []),
            'multiselect'  => $request->query->get('multiselect', false)
        ];
    }

    /**
     * @param  Opportunity $entity
     * @return array
     */
    protected function update(Opportunity $entity)
    {
        return $this->get('oro_form.model.update_handler')->update(
            $entity,
            $this->get('oro_sales.opportunity.form'),
            $this->get('translator')->trans('oro.sales.controller.opportunity.saved.message'),
            $this->get('oro_sales.opportunity.form.handler')
        );
    }

    /**
     * @return EntityRoutingHelper
     */
    protected function getEntityRoutingHelper()
    {
        return $this->get('oro_entity.routing_helper');
    }

    /**
     * @return AccountCustomerManager
     */
    protected function getAccountCustomerManager()
    {
        return $this->get('oro_sales.manager.account_customer');
    }
}
