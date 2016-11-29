<?php

namespace Oro\Bundle\SalesBundle\EventListener\Customers;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;

use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;
use Oro\Bundle\SalesBundle\Provider\Opportunity\DisplaySettingsConfigProvider;
use Oro\Bundle\UIBundle\Event\BeforeViewRenderEvent;

class OpportunitiesListener
{
    // below activity block which have 1000
    const GRID_BLOCK_PRIORITY = 1010;

    // opportunities grid rendered in template
    const GRID_NAME = 'sales-customers-opportunities-grid';

    /** @var ConfigProvider */
    protected $customerConfigProvider;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var RequestStack */
    protected $requestStack;

    /** @var DisplaySettingsConfigProvider */
    protected $opportunityDisplayConfigProvider;

    /**
     * @param ConfigProvider $customerConfigProvider
     * @param TranslatorInterface $translator
     * @param DoctrineHelper $helper
     * @param DisplaySettingsConfigProvider $opportunityDisplayConfigProvider
     */
    public function __construct(
        ConfigProvider $customerConfigProvider,
        TranslatorInterface $translator,
        DoctrineHelper $helper,
        RequestStack $requestStack,
        DisplaySettingsConfigProvider $opportunityDisplayConfigProvider
    ) {
        $this->customerConfigProvider           = $customerConfigProvider;
        $this->translator                       = $translator;
        $this->doctrineHelper                   = $helper;
        $this->requestStack                     = $requestStack;
        $this->opportunityDisplayConfigProvider = $opportunityDisplayConfigProvider;
    }

    /**
     * Adds block with associated opportunities grid of viewing entity
     * if this entity has "customer" association enabled.
     *
     * @param BeforeViewRenderEvent $event
     */
    public function addOpportunities(BeforeViewRenderEvent $event)
    {
        $entity = $event->getEntity();

        // Opportunity view related data and check
        $opportunityId = null;
        $displayOnOpportunityView = $this->opportunityDisplayConfigProvider->isFeatureEnabled();

        if ($entity instanceof Opportunity && $entity->getCustomerAssociation() && $displayOnOpportunityView) {
            $opportunityId = $this->doctrineHelper->getSingleEntityIdentifier($entity);
            $this->resetOpportunityIdGridParameter($opportunityId);
            $entity = $entity->getCustomerAssociation()->getAccount();
        }

        if ($this->customerConfigProvider->isCustomerClass($entity)) {
            $environment          = $event->getTwigEnvironment();
            $data                 = $event->getData();
            $titleKey = $opportunityId
                ? 'oro.sales.opportunity.relevant_opportunities'
                : 'oro.sales.customers.opportunities.grid.label';

            $opportunitiesData    = $environment->render(
                'OroSalesBundle:Customer:opportunitiesGrid.html.twig',
                ['gridParams' =>
                     [
                         'customer_id'    => $this->doctrineHelper->getSingleEntityIdentifier($entity),
                         'customer_class' => ClassUtils::getClass($entity),
                         'opportunity_id' => $opportunityId,
                     ]
                ]
            );
            $data['dataBlocks'][] = [
                'title'     => $this->translator->trans($titleKey),
                'priority' => self::GRID_BLOCK_PRIORITY,
                'subblocks' => [['data' => [$opportunitiesData]]]
            ];
            $event->setData($data);
        }
    }

    private function resetOpportunityIdGridParameter($opportunityId)
    {
        if (!$this->requestStack->getCurrentRequest()->query->has('grid')) {
            return;
        }

        $gridParams = $this->requestStack->getCurrentRequest()->query->get('grid');

        if (array_key_exists(self::GRID_NAME, $gridParams)) {
            $opportunityGridParams = $gridParams[self::GRID_NAME];
            if (!is_array($opportunityGridParams)) {
                $params = [];
                parse_str($opportunityGridParams, $params);
                $params['g']['opportunity_id'] = $opportunityId;
                $opportunityGridParams = http_build_query($params);
            } else {
                $opportunityGridParams['g']['opportunity_id'] = $opportunityId;
            }

            $gridParams[self::GRID_NAME] = $opportunityGridParams;
            $this->requestStack->getCurrentRequest()->query->set('grid', $gridParams);
        }
    }
}
