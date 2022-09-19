<?php

namespace Oro\Bundle\SalesBundle\Controller;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Form\Handler\LeadHandler;
use Oro\Bundle\SalesBundle\Form\Handler\LeadToOpportunityHandler;
use Oro\Bundle\SalesBundle\Form\Type\LeadToOpportunityType;
use Oro\Bundle\SalesBundle\Model\ChangeLeadStatus;
use Oro\Bundle\SalesBundle\Provider\LeadActionsAccessProvider;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\CsrfProtection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The controller for Lead entity.
 * @Route("/lead")
 */
class LeadController extends AbstractController
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
    public function viewAction(Lead $lead): array
    {
        $leadActionsAccessProvider = $this->get(LeadActionsAccessProvider::class);

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
    public function infoAction(Lead $lead): array
    {
        return array(
            'entity'  => $lead
        );
    }

    /**
     * Create lead form
     * @Route("/create", name="oro_sales_lead_create")
     * @Template("@OroSales/Lead/update.html.twig")
     * @Acl(
     *      id="oro_sales_lead_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroSalesBundle:Lead"
     * )
     */
    public function createAction(): array|RedirectResponse
    {
        return $this->update(new Lead());
    }

    /**
     * Update user form
     * @Route("/update/{id}", name="oro_sales_lead_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="oro_sales_lead_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroSalesBundle:Lead"
     * )
     */
    public function updateAction(Lead $entity): array|RedirectResponse
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
    public function indexAction(): array
    {
        return [
            'entity_class' => Lead::class
        ];
    }

    /**
     * @Route("/widget/account-leads/{id}", name="oro_sales_widget_account_leads", requirements={"id"="\d+"})
     * @AclAncestor("oro_sales_lead_view")
     * @Template()
     */
    public function accountLeadsAction(Account $account): array
    {
        return ['entity' => $account];
    }

    /**
     * Create lead form with data channel
     *
     * @Route("/create/{channelIds}", name="oro_sales_lead_data_channel_aware_create")
     * @Template("@OroSales/Lead/update.html.twig")
     * @AclAncestor("oro_sales_lead_view")
     * @ParamConverter(
     *      "channel",
     *      class="OroChannelBundle:Channel",
     *      options={"id" = "channelIds"}
     * )
     */
    public function leadWithDataChannelCreateAction(Channel $channel): array|RedirectResponse
    {
        $lead = new Lead();
        $lead->setDataChannel($channel);

        return $this->update($lead);
    }

    /**
     * Change status for lead
     *
     * @CsrfProtection
     * @Route("/disqualify/{id}", name="oro_sales_lead_disqualify", requirements={"id"="\d+"}, methods={"POST"})
     * @Acl(
     *      id="oro_sales_lead_disqualify",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroSalesBundle:Lead"
     * )
     */
    public function disqualifyAction(Lead $lead, Request $request): JsonResponse
    {
        if (!$this->get(LeadActionsAccessProvider::class)->isDisqualifyAllowed($lead)) {
            throw new AccessDeniedException();
        }

        if (!$this->get(ChangeLeadStatus::class)->disqualify($lead)) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $request->getSession()->getFlashBag()->add(
            'success',
            $this->get(TranslatorInterface::class)->trans('oro.sales.controller.lead.saved.message')
        );

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
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
    public function convertToOpportunityAction(Lead $lead, Request $request): array|RedirectResponse
    {
        if (!$this->get(LeadActionsAccessProvider::class)->isConvertToOpportunityAllowed($lead)) {
            throw new AccessDeniedException('Lead couldn\'t be converted to opportunity!');
        }

        return $this->get(LeadToOpportunityHandler::class)->create(
            $lead,
            $this->createForm(LeadToOpportunityType::class),
            $this->get(TranslatorInterface::class)->trans('oro.sales.controller.opportunity.saved.message'),
            $request,
            $this->get(UpdateHandlerFacade::class),
            function () use ($request) {
                $request->getSession()->getFlashBag()->add(
                    'error',
                    $this->get(TranslatorInterface::class)->trans('oro.sales.lead.convert.error')
                );
            }
        );
    }

    protected function update(Lead $entity): array|RedirectResponse
    {
        return $this->get(UpdateHandlerFacade::class)->update(
            $entity,
            $this->get('oro_sales.lead.form'),
            $this->get(TranslatorInterface::class)->trans('oro.sales.controller.lead.saved.message'),
            null,
            $this->get(LeadHandler::class)
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                LeadActionsAccessProvider::class,
                ChangeLeadStatus::class,
                LeadToOpportunityHandler::class,
                LeadHandler::class,
                'oro_sales.lead.form' => Form::class,
                UpdateHandlerFacade::class
            ]
        );
    }
}
