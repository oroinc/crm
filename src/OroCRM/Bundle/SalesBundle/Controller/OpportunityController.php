<?php

namespace OroCRM\Bundle\SalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

/**
 * @Route("/opportunity")
 */
class OpportunityController extends Controller
{
    /**
     * @Route("/view/{id}", name="orocrm_sales_opportunity_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="orocrm_sales_opportunity_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMSalesBundle:Opportunity"
     * )
     */
    public function viewAction(Opportunity $entity)
    {
        return array(
            'entity' => $entity,
        );
    }

    /**
     * @Route("/info/{id}", name="orocrm_sales_opportunity_info", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("orocrm_sales_opportunity_view")
     */
    public function infoAction(Opportunity $entity)
    {
        return array(
            'entity'  => $entity
        );
    }

    /**
     * @Route("/create", name="orocrm_sales_opportunity_create")
     * @Template("OroCRMSalesBundle:Opportunity:update.html.twig")
     * @Acl(
     *      id="orocrm_sales_opportunity_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMSalesBundle:Opportunity"
     * )
     */
    public function createAction()
    {
        return $this->update(new Opportunity());
    }

    /**
     * @Route("/update/{id}", name="orocrm_sales_opportunity_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="orocrm_sales_opportunity_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMSalesBundle:Opportunity"
     * )
     */
    public function updateAction(Opportunity $entity)
    {
        return $this->update($entity);
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="orocrm_sales_opportunity_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     * @AclAncestor("orocrm_sales_opportunity_view")
     */
    public function indexAction()
    {
        return [
            'entity_class' => $this->container->getParameter('orocrm_sales.opportunity.class')
        ];
    }

    /**
     * Create opportunity form with data channel
     *
     * @Route("/create/{channelIds}", name="orocrm_sales_opportunity_data_channel_aware_create")
     * @Template("OroCRMSalesBundle:Opportunity:update.html.twig")
     * @AclAncestor("orocrm_sales_opportunity_create")
     *
     * @ParamConverter(
     *      "channel",
     *      class="OroCRMChannelBundle:Channel",
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
     * @Route(
     *     "/datagrid/opportunity-with-datachannel/{channelIds}",
     *     name="orocrm_sales_datagrid_opportunity_datachannel_aware"
     * )
     * @Template("OroCRMSalesBundle:Widget:entityWithDataChannelGrid.html.twig")
     * @AclAncestor("orocrm_sales_opportunity_view")
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
            $this->get('orocrm_sales.opportunity.form'),
            $this->get('translator')->trans('orocrm.sales.controller.opportunity.saved.message'),
            $this->get('orocrm_sales.opportunity.form.handler')
        );
    }
}
