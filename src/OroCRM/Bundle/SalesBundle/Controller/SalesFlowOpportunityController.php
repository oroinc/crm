<?php

namespace OroCRM\Bundle\SalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use OroCRM\Bundle\SalesBundle\Entity\SalesFlowOpportunity;

/**
 * @Route("/sales_flow_opportunity")
 */
class SalesFlowOpportunityController extends Controller
{
    /**
     * @Route("/view/{id}", name="orocrm_sales_sales_flow_opportunity_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="orocrm_sales_sales_flow_opportunity_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMSalesBundle:SalesFlowOpportunity"
     * )
     */
    public function viewAction(SalesFlowOpportunity $entity)
    {
        return array(
            'entity' => $entity,
        );
    }

    /**
     * @Route("/info/{id}", name="orocrm_sales_sales_flow_opportunity_info", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("orocrm_sales_sales_flow_opportunity_view")
     */
    public function infoAction(SalesFlowOpportunity $entity)
    {
        return array(
            'entity' => $entity
        );
    }

    /**
     * @Route("/create", name="orocrm_sales_sales_flow_opportunity_create")
     * @Template("OroCRMSalesBundle:Opportunity:update.html.twig")
     * @Acl(
     *      id="orocrm_sales_sales_flow_opportunity_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMSalesBundle:SalesFlowOpportunity"
     * )
     */
    public function createAction()
    {
        $entity = new SalesFlowOpportunity();

        return $this->update($entity);
    }

    /**
     * @Route("/update/{id}", name="orocrm_sales_sales_flow_opportunity_update", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="orocrm_sales_sales_flow_opportunity_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMSalesBundle:SalesFlowOpportunity"
     * )
     */
    public function updateAction(SalesFlowOpportunity $entity)
    {
        return $this->update($entity);
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="orocrm_sales_sales_flow_opportunity_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     * @AclAncestor("orocrm_sales_sales_opportunity_view")
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @param SalesFlowOpportunity $entity
     * @return array
     */
    protected function update(SalesFlowOpportunity $entity)
    {
        if ($this->get('orocrm_sales.opportunity.form.handler')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('orocrm.sales.controller.sales_flow_opportunity.saved.message')
            );

            return $this->get('oro_ui.router')->actionRedirect(
                array(
                    'route'      => 'orocrm_sales_opportunity_update',
                    'parameters' => array('id' => $entity->getId()),
                ),
                array(
                    'route'      => 'orocrm_sales_opportunity_view',
                    'parameters' => array('id' => $entity->getId()),
                )
            );
        }

        return array(
            'entity' => $entity,
            'form'   => $this->get('orocrm_sales.opportunity.form')->createView(),
        );
    }
}
