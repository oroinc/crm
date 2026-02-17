<?php

namespace Oro\Bundle\SalesBundle\Controller;

use Doctrine\ORM\EntityNotFoundException;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\FormBundle\Provider\FormTemplateDataProviderInterface;
use Oro\Bundle\FormBundle\Provider\SaveAndReturnActionFormTemplateDataProvider;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Form\Handler\OpportunityHandler;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Handles opportunity entity CRUD and getting info actions
 */
#[Route(path: '/opportunity')]
class OpportunityController extends AbstractController
{
    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'oro_sales.opportunity.form' => Form::class,
            OpportunityHandler::class,
            TranslatorInterface::class,
            AccountCustomerManager::class,
            EntityRoutingHelper::class,
            UpdateHandlerFacade::class,
            SaveAndReturnActionFormTemplateDataProvider::class
        ]);
    }

    #[Route(path: '/view/{id}', name: 'oro_sales_opportunity_view', requirements: ['id' => '\d+'])]
    #[Template('@OroSales/Opportunity/view.html.twig')]
    #[Acl(id: 'oro_sales_opportunity_view', type: 'entity', class: Opportunity::class, permission: 'VIEW')]
    public function viewAction(Opportunity $entity): array
    {
        return [
            'entity' => $entity,
        ];
    }

    #[Route(path: '/info/{id}', name: 'oro_sales_opportunity_info', requirements: ['id' => '\d+'])]
    #[Template('@OroSales/Opportunity/info.html.twig')]
    #[AclAncestor('oro_sales_opportunity_view')]
    public function infoAction(Opportunity $entity): array
    {
        return [
            'entity'  => $entity
        ];
    }

    #[Route(path: '/create', name: 'oro_sales_opportunity_create')]
    #[Template('@OroSales/Opportunity/update.html.twig')]
    #[Acl(id: 'oro_sales_opportunity_create', type: 'entity', class: Opportunity::class, permission: 'CREATE')]
    public function createAction(): array|RedirectResponse
    {
        return $this->update(new Opportunity());
    }

    #[Route(
        path: '/update/{id}',
        name: 'oro_sales_opportunity_update',
        requirements: ['id' => '\d+'],
        defaults: ['id' => 0]
    )]
    #[Template('@OroSales/Opportunity/update.html.twig')]
    #[Acl(id: 'oro_sales_opportunity_update', type: 'entity', class: Opportunity::class, permission: 'EDIT')]
    public function updateAction(Opportunity $entity): array|RedirectResponse
    {
        return $this->update($entity);
    }

    #[Route(
        path: '/{_format}',
        name: 'oro_sales_opportunity_index',
        requirements: ['_format' => 'html|json'],
        defaults: ['_format' => 'html']
    )]
    #[Template('@OroSales/Opportunity/index.html.twig')]
    #[AclAncestor('oro_sales_opportunity_view')]
    public function indexAction(): array
    {
        return [
            'entity_class' => Opportunity::class
        ];
    }

    /**
     * Create opportunity form with data channel
     *
     *
     */
    #[Route(path: '/create/{channelIds}', name: 'oro_sales_opportunity_data_channel_aware_create')]
    #[Template('@OroSales/Opportunity/update.html.twig')]
    #[AclAncestor('oro_sales_opportunity_create')]
    public function opportunityWithDataChannelCreateAction(
        #[MapEntity(id: 'channelIds')]
        Channel $channel
    ): array|RedirectResponse {
        $opportunity = new Opportunity();
        $opportunity->setDataChannel($channel);

        return $this->update($opportunity);
    }

    /**
     * Create opportunity form with customer association set
     *
     * @throws EntityNotFoundException
     */
    #[Route(path: '/create/{targetClass}/{targetId}', name: 'oro_sales_opportunity_customer_aware_create')]
    #[Template('@OroSales/Opportunity/update.html.twig')]
    #[AclAncestor('oro_sales_opportunity_create')]
    public function opportunityWithCustomerCreateAction($targetClass, $targetId): array|RedirectResponse
    {
        $target = $this->container->get(EntityRoutingHelper::class)->getEntity($targetClass, $targetId);
        if (!$this->isGranted('VIEW', $target)) {
            throw $this->createAccessDeniedException();
        }

        $customer = $this->container->get(AccountCustomerManager::class)->getAccountCustomerByTarget($target);

        $opportunity = new Opportunity();
        $opportunity->setCustomerAssociation($customer);

        $saveAndReturnActionFormTemplateDataProvider = $this->container
            ->get(SaveAndReturnActionFormTemplateDataProvider::class);
        $saveAndReturnActionFormTemplateDataProvider
            ->setSaveFormActionRoute(
                'oro_sales_opportunity_customer_aware_create',
                [
                    'targetClass' => $targetClass,
                    'targetId' => $targetId,
                ]
            )
            ->setReturnActionRoute(
                'oro_customer_customer_view',
                [
                    'id' => $targetId,
                ],
                'oro_customer_customer_view'
            );

        return $this->update($opportunity, $saveAndReturnActionFormTemplateDataProvider);
    }

    protected function update(
        Opportunity $entity,
        FormTemplateDataProviderInterface|null $resultProvider = null
    ): array|RedirectResponse {
        return $this->container->get(UpdateHandlerFacade::class)->update(
            $entity,
            $this->container->get('oro_sales.opportunity.form'),
            $this->container->get(TranslatorInterface::class)->trans('oro.sales.controller.opportunity.saved.message'),
            null,
            $this->container->get(OpportunityHandler::class),
            $resultProvider
        );
    }
}
