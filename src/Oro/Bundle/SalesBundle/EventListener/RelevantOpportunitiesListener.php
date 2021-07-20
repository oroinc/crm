<?php

namespace Oro\Bundle\SalesBundle\EventListener;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Provider\Opportunity\DisplaySettingsConfigProvider;
use Oro\Bundle\UIBundle\Event\BeforeViewRenderEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Adds block with relevant opportunities grid on the Opportunity view.
 */
class RelevantOpportunitiesListener
{
    // below additional information block which have 1200
    const GRID_BLOCK_PRIORITY = 1210;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var RequestStack */
    protected $requestStack;

    /** @var DisplaySettingsConfigProvider */
    protected $opportunityDisplayConfigProvider;

    public function __construct(
        TranslatorInterface $translator,
        RequestStack $requestStack,
        DisplaySettingsConfigProvider $opportunityDisplayConfigProvider
    ) {
        $this->translator                       = $translator;
        $this->requestStack                     = $requestStack;
        $this->opportunityDisplayConfigProvider = $opportunityDisplayConfigProvider;
    }

    /**
     * Adds block with relevant opportunities grid on the Opportunity view
     */
    public function addRelevantOpportunities(BeforeViewRenderEvent $event)
    {
        $data        = $event->getData();
        $entity      = $event->getEntity();
        $environment = $event->getTwigEnvironment();

        if (!$this->opportunityDisplayConfigProvider->isFeatureEnabled()) {
            return;
        }

        if (!$entity instanceof Opportunity) {
            return;
        }

        if (!$entity->getCustomerAssociation()) {
            return;
        }

        $account           = $entity->getCustomerAssociation()->getAccount();
        $opportunitiesData = $environment->render(
            '@OroSales/Opportunity/relevantOpportunities.html.twig',
            [
                'gridParams' =>
                    [
                        'customer_id'    => $account->getId(),
                        'customer_class' => Account::class,
                        'related_entity_class' => Opportunity::class,
                        'opportunity_id' => $entity->getId(),
                    ]
            ]
        );

        $data['dataBlocks'][] = [
            'title'     => $this->translator->trans('oro.sales.opportunity.relevant_opportunities'),
            'priority'  => self::GRID_BLOCK_PRIORITY,
            'subblocks' => [['data' => [$opportunitiesData]]]
        ];

        $event->setData($data);
    }
}
