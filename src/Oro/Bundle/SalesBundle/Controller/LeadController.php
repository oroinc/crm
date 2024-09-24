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
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\SecurityBundle\Attribute\CsrfProtection;
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
 */
#[Route(path: '/lead')]
class LeadController extends AbstractController
{
    #[Route(path: '/view/{id}', name: 'oro_sales_lead_view', requirements: ['id' => '\d+'])]
    #[Template]
    #[Acl(id: 'oro_sales_lead_view', type: 'entity', class: Lead::class, permission: 'VIEW')]
    public function viewAction(Lead $lead): array
    {
        $leadActionsAccessProvider = $this->container->get(LeadActionsAccessProvider::class);

        return [
            'entity'                        => $lead,
            'isDisqualifyAllowed'           => $leadActionsAccessProvider->isDisqualifyAllowed($lead),
            'isConvertToOpportunityAllowed' => $leadActionsAccessProvider->isConvertToOpportunityAllowed($lead)
        ];
    }

    #[Route(path: '/info/{id}', name: 'oro_sales_lead_info', requirements: ['id' => '\d+'])]
    #[Template]
    #[AclAncestor('oro_sales_lead_view')]
    public function infoAction(Lead $lead): array
    {
        return array(
            'entity'  => $lead
        );
    }

    /**
     * Create lead form
     */
    #[Route(path: '/create', name: 'oro_sales_lead_create')]
    #[Template('@OroSales/Lead/update.html.twig')]
    #[Acl(id: 'oro_sales_lead_create', type: 'entity', class: Lead::class, permission: 'CREATE')]
    public function createAction(): array|RedirectResponse
    {
        return $this->update(new Lead());
    }

    /**
     * Update user form
     */
    #[Route(path: '/update/{id}', name: 'oro_sales_lead_update', requirements: ['id' => '\d+'], defaults: ['id' => 0])]
    #[Template]
    #[Acl(id: 'oro_sales_lead_update', type: 'entity', class: Lead::class, permission: 'EDIT')]
    public function updateAction(Lead $entity): array|RedirectResponse
    {
        return $this->update($entity);
    }

    #[Route(
        path: '/{_format}',
        name: 'oro_sales_lead_index',
        requirements: ['_format' => 'html|json'],
        defaults: ['_format' => 'html']
    )]
    #[Template]
    #[AclAncestor('oro_sales_lead_view')]
    public function indexAction(): array
    {
        return [
            'entity_class' => Lead::class
        ];
    }

    #[Route(path: '/widget/account-leads/{id}', name: 'oro_sales_widget_account_leads', requirements: ['id' => '\d+'])]
    #[Template]
    #[AclAncestor('oro_sales_lead_view')]
    public function accountLeadsAction(Account $account): array
    {
        return ['entity' => $account];
    }

    /**
     * Create lead form with data channel
     */
    #[Route(path: '/create/{channelIds}', name: 'oro_sales_lead_data_channel_aware_create')]
    #[Template('@OroSales/Lead/update.html.twig')]
    #[ParamConverter('channel', class: Channel::class, options: ['id' => 'channelIds'])]
    #[AclAncestor('oro_sales_lead_view')]
    public function leadWithDataChannelCreateAction(Channel $channel): array|RedirectResponse
    {
        $lead = new Lead();
        $lead->setDataChannel($channel);

        return $this->update($lead);
    }

    /**
     * Change status for lead
     */
    #[Route(
        path: '/disqualify/{id}',
        name: 'oro_sales_lead_disqualify',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    #[Acl(id: 'oro_sales_lead_disqualify', type: 'entity', class: Lead::class, permission: 'EDIT')]
    #[CsrfProtection()]
    public function disqualifyAction(Lead $lead, Request $request): JsonResponse
    {
        if (!$this->container->get(LeadActionsAccessProvider::class)->isDisqualifyAllowed($lead)) {
            throw new AccessDeniedException();
        }

        if (!$this->container->get(ChangeLeadStatus::class)->disqualify($lead)) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $request->getSession()->getFlashBag()->add(
            'success',
            $this->container->get(TranslatorInterface::class)->trans('oro.sales.controller.lead.saved.message')
        );

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route(path: '/convert/{id}', name: 'oro_sales_lead_convert_to_opportunity', requirements: ['id' => '\d+'])]
    #[Template]
    #[Acl(id: 'oro_sales_lead_convert_to_opportunity', type: 'entity', class: Lead::class, permission: 'EDIT')]
    public function convertToOpportunityAction(Lead $lead, Request $request): array|RedirectResponse
    {
        if (!$this->container->get(LeadActionsAccessProvider::class)->isConvertToOpportunityAllowed($lead)) {
            throw new AccessDeniedException('Lead couldn\'t be converted to opportunity!');
        }

        return $this->container->get(LeadToOpportunityHandler::class)->create(
            $lead,
            $this->createForm(LeadToOpportunityType::class),
            $this->container->get(TranslatorInterface::class)->trans('oro.sales.controller.opportunity.saved.message'),
            $request,
            $this->container->get(UpdateHandlerFacade::class),
            function () use ($request) {
                $request->getSession()->getFlashBag()->add(
                    'error',
                    $this->container->get(TranslatorInterface::class)->trans('oro.sales.lead.convert.error')
                );
            }
        );
    }

    protected function update(Lead $entity): array|RedirectResponse
    {
        return $this->container->get(UpdateHandlerFacade::class)->update(
            $entity,
            $this->container->get('oro_sales.lead.form'),
            $this->container->get(TranslatorInterface::class)->trans('oro.sales.controller.lead.saved.message'),
            null,
            $this->container->get(LeadHandler::class)
        );
    }

    #[\Override]
    public static function getSubscribedServices(): array
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
