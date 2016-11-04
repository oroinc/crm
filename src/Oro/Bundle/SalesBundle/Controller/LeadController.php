<?php

namespace Oro\Bundle\SalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\ChannelBundle\Entity\Channel;

/**
 * @Route("/lead")
 */
class LeadController extends Controller
{
    /**
     * @Route("/view/{id}", name="oro_sales_lead_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_sales_lead_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroSalesBundle:Lead"
     * )
     */
    public function viewAction(Lead $lead)
    {
        $leadActionsAccessProvider = $this->get('oro_sales.provider.lead_actions_access');

        return [
            'entity'                        => $lead,
            'isDisqualifyAllowed'           => $leadActionsAccessProvider->isDisqualifyAllowed($lead),
            'isConvertToOpportunityAllowed' => $leadActionsAccessProvider->isConvertToOpportunityAllowed($lead)
        ];
    }

    /**
     * @Route("/info/{id}", name="oro_sales_lead_info", requirements={"id"="\d+"})
     * @AclAncestor("oro_sales_lead_view")
     * @Template()
     */
    public function infoAction(Lead $lead)
    {
        return array(
            'entity'  => $lead
        );
    }

    /**
     * Create lead form
     * @Route("/create", name="oro_sales_lead_create")
     * @Template("OroSalesBundle:Lead:update.html.twig")
     * @Acl(
     *      id="oro_sales_lead_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroSalesBundle:Lead"
     * )
     */
    public function createAction()
    {
        return $this->update(new Lead());
    }

    /**
     * Update user form
     * @Route("/update/{id}", name="oro_sales_lead_update", requirements={"id"="\d+"}, defaults={"id"=0})
     *
     * @Template
     * @Acl(
     *      id="oro_sales_lead_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroSalesBundle:Lead"
     * )
     */
    public function updateAction(Lead $entity)
    {
        return $this->update($entity);
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_sales_lead_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     * @AclAncestor("oro_sales_lead_view")
     */
    public function indexAction()
    {
        return [
            'entity_class' => $this->container->getParameter('oro_sales.lead.entity.class')
        ];
    }

    /**
     * @Route("/widget/account-leads/{id}", name="oro_sales_widget_account_leads", requirements={"id"="\d+"})
     * @AclAncestor("oro_sales_lead_view")
     * @Template()
     */
    public function accountLeadsAction(Account $account)
    {
        return array('entity' => $account);
    }

    /**
     * Create lead form with data channel
     *
     * @Route("/create/{channelIds}", name="oro_sales_lead_data_channel_aware_create")
     * @Template("OroSalesBundle:Lead:update.html.twig")
     * @AclAncestor("oro_sales_lead_view")
     *
     * @ParamConverter(
     *      "channel",
     *      class="OroChannelBundle:Channel",
     *      options={"id" = "channelIds"}
     * )
     */
    public function leadWithDataChannelCreateAction(Channel $channel)
    {
        $lead = new Lead();
        $lead->setDataChannel($channel);

        return $this->update($lead);
    }

    /**
     * @Route("/datagrid/lead-with-datachannel/{channelIds}", name="oro_sales_datagrid_lead_datachannel_aware")
     * @Template("OroSalesBundle:Widget:entityWithDataChannelGrid.html.twig")
     * @AclAncestor("oro_sales_lead_view")
     */
    public function leadWithDataChannelGridAction($channelIds, Request $request)
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
     * Change status for lead
     *
     * @Route("/disqualify/{id}", name="oro_sales_lead_disqualify", requirements={"id"="\d+"})
     * @Acl(
     *      id="oro_sales_lead_disqualify",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroSalesBundle:Lead"
     * )
     */
    public function disqualifyAction(Lead $lead)
    {
        if (!$this->get('oro_sales.provider.lead_actions_access')->isDisqualifyAllowed($lead)) {
            throw new AccessDeniedException();
        }
        
        if ($this->get('oro_sales.model.change_lead_status')->disqualify($lead)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('oro.sales.controller.lead.saved.message')
            );
        } else {
            $this->get('session')->getFlashBag()->add(
                'error',
                $this->get('translator')->trans('oro.sales.lead.status.change_error_message')
            );
        }

        return $this->redirectToRoute('oro_sales_lead_view', ['id' => $lead->getId()]);
    }

    /**
     * @Route("/convert/{id}", name="oro_sales_lead_convert_to_opportunity", requirements={"id"="\d+"})
     * @Acl(
     *      id="oro_sales_lead_convert_to_opportunity",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroSalesBundle:Lead"
     * )
     * @Template()
     */
    public function convertToOpportunityAction(Lead $lead)
    {
        if (!$this->get('oro_sales.provider.lead_actions_access')->isConvertToOpportunityAllowed($lead)) {
            throw new AccessDeniedException('Lead couldn\'t be converted to opportunity!');
        }

        $session = $this->get('session');
        return $this->get('oro_sales.lead_to_opportunity.form.handler')->create(
            $lead,
            $this->get('oro_form.model.update_handler'),
            $this->get('translator')->trans('oro.sales.controller.opportunity.saved.message'),
            function () use ($session) {
                $session->getFlashBag()->add(
                    'error',
                    $this->get('translator')->trans('oro.sales.lead.convert.error')
                );
            }
        );
    }

    /**
     * @param Lead $entity
     *
     * @return array
     */
    protected function update(Lead $entity)
    {
        return $this->get('oro_form.model.update_handler')->update(
            $entity,
            $this->get('oro_sales.lead.form'),
            $this->get('translator')->trans('oro.sales.controller.lead.saved.message'),
            $this->get('oro_sales.lead.form.handler')
        );
    }
}
