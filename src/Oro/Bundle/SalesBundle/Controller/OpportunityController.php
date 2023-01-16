<?php

namespace Oro\Bundle\SalesBundle\Controller;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\FormBundle\Provider\FormTemplateDataProviderInterface;
use Oro\Bundle\FormBundle\Provider\SaveAndReturnActionFormTemplateDataProvider;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Form\Handler\OpportunityHandler;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Handles opportunity entity CRUD and getting info actions
 * @Route("/opportunity")
 */
class OpportunityController extends AbstractController
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), [
            OpportunityHandler::class,
            TranslatorInterface::class,
            'oro_sales.opportunity.form' => Form::class,
            AccountCustomerManager::class,
            EntityRoutingHelper::class,
            UpdateHandlerFacade::class,
            SaveAndReturnActionFormTemplateDataProvider::class,
        ]);
    }

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
    public function viewAction(Opportunity $entity): array
    {
        return [
            'entity' => $entity,
        ];
    }

    /**
     * @Route("/info/{id}", name="oro_sales_opportunity_info", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("oro_sales_opportunity_view")
     */
    public function infoAction(Opportunity $entity): array
    {
        return [
            'entity'  => $entity
        ];
    }

    /**
     * @Route("/create", name="oro_sales_opportunity_create")
     * @Template("@OroSales/Opportunity/update.html.twig")
     * @Acl(
     *      id="oro_sales_opportunity_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroSalesBundle:Opportunity"
     * )
     */
    public function createAction(): array|RedirectResponse
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
    public function updateAction(Opportunity $entity): array|RedirectResponse
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
    public function indexAction(): array
    {
        return [
            'entity_class' => Opportunity::class
        ];
    }

    /**
     * Create opportunity form with data channel
     *
     * @Route("/create/{channelIds}", name="oro_sales_opportunity_data_channel_aware_create")
     * @Template("@OroSales/Opportunity/update.html.twig")
     * @AclAncestor("oro_sales_opportunity_create")
     *
     * @ParamConverter(
     *      "channel",
     *      class="OroChannelBundle:Channel",
     *      options={"id" = "channelIds"}
     * )
     */
    public function opportunityWithDataChannelCreateAction(Channel $channel): array|RedirectResponse
    {
        $opportunity = new Opportunity();
        $opportunity->setDataChannel($channel);

        return $this->update($opportunity);
    }

    /**
     * Create opportunity form with customer association set
     *
     * @Route("/create/{targetClass}/{targetId}", name="oro_sales_opportunity_customer_aware_create")
     * @Template("@OroSales/Opportunity/update.html.twig")
     * @AclAncestor("oro_sales_opportunity_create")
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function opportunityWithCustomerCreateAction($targetClass, $targetId): array|RedirectResponse
    {
        $target = $this->get(EntityRoutingHelper::class)->getEntity($targetClass, $targetId);
        if (!$this->isGranted('VIEW', $target)) {
            throw $this->createAccessDeniedException();
        }

        $customer = $this->get(AccountCustomerManager::class)->getAccountCustomerByTarget($target);

        $opportunity = new Opportunity();
        $opportunity->setCustomerAssociation($customer);

        $saveAndReturnActionFormTemplateDataProvider = $this->get(SaveAndReturnActionFormTemplateDataProvider::class);
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
        return $this->get(UpdateHandlerFacade::class)->update(
            $entity,
            $this->get('oro_sales.opportunity.form'),
            $this->get(TranslatorInterface::class)->trans('oro.sales.controller.opportunity.saved.message'),
            null,
            $this->get(OpportunityHandler::class),
            $resultProvider
        );
    }
}
