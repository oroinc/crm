<?php

namespace OroCRM\Bundle\SalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroCRM\Bundle\SalesBundle\Entity\Lead;

/**
 * @Route("/lead")
 */
class LeadController extends Controller
{
    /**
     * @Route("/view/{id}", name="orocrm_sales_lead_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="orocrm_sales_lead_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMSalesBundle:Lead"
     * )
     */
    public function viewAction(Lead $lead)
    {
        return array(
            'entity' => $lead
        );
    }

    /**
     * @Route("/info/{id}", name="orocrm_sales_lead_info", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("orocrm_sales_lead_view")
     */
    public function infoAction(Lead $lead)
    {
        return array(
            'entity'  => $lead
        );
    }

    /**
     * @Route("/address-book/{id}", name="orocrm_sales_lead_address_book", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("orocrm_sales_lead_view")
     */
    public function addressBookAction(Lead $lead)
    {
        return array(
            'entity' => $lead
        );
    }

    /**
     * Create lead form
     * @Route("/create", name="orocrm_sales_lead_create")
     * @Template("OroCRMSalesBundle:Lead:update.html.twig")
     * @Acl(
     *      id="orocrm_sales_lead_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMSalesBundle:Lead"
     * )
     */
    public function createAction()
    {
        $lead          = new Lead();
        $defaultStatus = $this->getDoctrine()->getManager()->find('OroCRMSalesBundle:LeadStatus', 'new');
        $lead->setStatus($defaultStatus);

        return $this->update($lead);
    }

    /**
     * Update user form
     * @Route("/update/{id}", name="orocrm_sales_lead_update", requirements={"id"="\d+"}, defaults={"id"=0})
     *
     * @Template
     * @Acl(
     *      id="orocrm_sales_lead_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMSalesBundle:Lead"
     * )
     */
    public function updateAction(Lead $entity)
    {
        return $this->update($entity);
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="orocrm_sales_lead_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     * @AclAncestor("orocrm_sales_lead_view")
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @param Lead $entity
     *
     * @return array
     */
    protected function update(Lead $entity)
    {
        if ($this->get('orocrm_sales.lead.form.handler')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('orocrm.sales.controller.lead.saved.message')
            );

            return $this->get('oro_ui.router')->redirectAfterSave(
                ['route' => 'orocrm_sales_lead_update', 'parameters' => ['id' => $entity->getId()]],
                ['route' => 'orocrm_sales_lead_view', 'parameters' => ['id' => $entity->getId()]],
                $entity
            );
        }

        return array(
            'entity' => $entity,
            'form'   => $this->get('orocrm_sales.lead.form')->createView()
        );
    }
}
