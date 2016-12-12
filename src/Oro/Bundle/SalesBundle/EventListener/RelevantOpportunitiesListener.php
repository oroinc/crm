<?php

namespace Oro\Bundle\SalesBundle\EventListener;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Provider\Opportunity\DisplaySettingsConfigProvider;
use Oro\Bundle\UIBundle\Event\BeforeViewRenderEvent;

class RelevantOpportunitiesListener
{
    // below activity block which have 1000
    const GRID_BLOCK_PRIORITY = 1010;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var RequestStack */
    protected $requestStack;

    /** @var DisplaySettingsConfigProvider */
    protected $opportunityDisplayConfigProvider;

    /**
     * @param TranslatorInterface           $translator
     * @param RequestStack                  $requestStack
     * @param DisplaySettingsConfigProvider $opportunityDisplayConfigProvider
     */
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
     *
     * @param BeforeViewRenderEvent $event
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
            'OroSalesBundle:Opportunity:relevantOpportunities.html.twig',
            [
                'gridParams' =>
                    [
                        'customer_id'    => $account->getId(),
                        'customer_class' => Account::class,
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
