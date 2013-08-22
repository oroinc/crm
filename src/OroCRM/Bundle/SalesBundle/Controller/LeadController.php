<?php

namespace OroCRM\Bundle\SalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;

use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Datagrid\LeadDatagridManager;

/**
 *
 * @Route("/lead")
 * @Acl(
 *      id="orocrm_sales_lead",
 *      name="Lead manipulation",
 *      description="Lead manipulation",
 *      parent="root"
 * )
 */
class LeadController extends Controller
{
    /**
     * @Route("/view/{id}", name="orocrm_sales_lead_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="orocrm_sales_lead_view",
     *      name="View Lead",
     *      description="View lead",
     *      parent="orocrm_sales_lead"
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
            'entity' => $lead
        );
    }

    /**
     * Create lead form
     *
     * @Route("/create", name="orocrm_sales_lead_create")
     * @Template("OroCRMSalesBundle:Lead:update.html.twig")
     * @Acl(
     *      id="orocrm_sales_lead_create",
     *      name="Create Lead",
     *      description="Create lead",
     *      parent="orocrm_sales_lead"
     * )
     */
    public function createAction()
    {
        return $this->updateAction(new Lead());
    }

    /**
     * Update user form
     *
     * @Route("/update/{id}", name="orocrm_sales_lead_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="orocrm_sales_lead_update",
     *      name="Update Lead",
     *      description="Update lead",
     *      parent="orocrm_sales_lead"
     * )
     */
    public function updateAction(Lead $entity)
    {
        if ($this->get('orocrm_sales.lead.form.handler')->process($entity)) {
            $this->getFlashBag()->add('success', 'Lead successfully saved');

            return $this->get('oro_ui.router')->actionRedirect(
                array(
                    'route' => 'orocrm_sales_lead_update',
                    'parameters' => array('id' => $entity->getId()),
                ),
                array(
                    'route' => 'orocrm_sales_lead_index',
                )
            );
        }

        return array(
            'entity'   => $entity,
            'form'     => $this->get('orocrm_sales.lead.form')->createView()
        );
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="orocrm_sales_lead_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     * @Acl(
     *      id="orocrm_sales_lead_list",
     *      name="View List of Leads",
     *      description="View list of leads",
     *      parent="orocrm_sales_lead"
     * )
     */
    public function indexAction()
    {
        /** @var $gridManager LeadDatagridManager */
        $gridManager = $this->get('orocrm_sales.lead.datagrid_manager');
        $datagridView = $gridManager->getDatagrid()->createView();

        if ('json' == $this->getRequest()->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
        }

        return array('datagrid' => $datagridView);
    }

    /**
     * @return FlashBag
     */
    protected function getFlashBag()
    {
        return $this->get('session')->getFlashBag();
    }
}
