<?php

namespace Oro\Bundle\SalesBundle\EventListener\Customers;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider as CustomerConfigProvider;
use Oro\Bundle\UIBundle\Event\BeforeViewRenderEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Adds block with associated opportunities grid of viewing entity.
 */
class OpportunitiesListener
{
    // below activity block which have 1000
    const DEFAULT_GRID_BLOCK_PRIORITY = 1010;

    /** @var CustomerConfigProvider */
    protected $customerConfigProvider;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var  ConfigProvider */
    protected $configProvider;

    /** @var  FeatureChecker */
    protected $featureChecker;

    public function __construct(
        CustomerConfigProvider $customerConfigProvider,
        TranslatorInterface $translator,
        DoctrineHelper $helper,
        ConfigProvider $configProvider,
        FeatureChecker $featureChecker
    ) {
        $this->customerConfigProvider = $customerConfigProvider;
        $this->translator             = $translator;
        $this->doctrineHelper         = $helper;
        $this->configProvider         = $configProvider;
        $this->featureChecker         = $featureChecker;
    }

    /**
     * Adds block with associated opportunities grid of viewing entity
     * if this entity has "customer" association enabled.
     */
    public function addOpportunities(BeforeViewRenderEvent $event)
    {
        if (!$this->featureChecker->isFeatureEnabled('sales_opportunity')) {
            return;
        }

        $entity = $event->getEntity();
        if ($this->customerConfigProvider->isCustomerClass($entity)) {
            $environment          = $event->getTwigEnvironment();
            $data                 = $event->getData();
            $targetClass          = ClassUtils::getClass($entity);
            $priority             = $this->getBlockPriority($targetClass);
            $opportunitiesData    = $environment->render(
                '@OroSales/Customer/opportunitiesGrid.html.twig',
                [
                    'gridParams' =>
                         [
                             'customer_id'    => $this->doctrineHelper->getSingleEntityIdentifier($entity),
                             'customer_class' => $targetClass,
                             'related_entity_class' => Opportunity::class,
                         ]
                ]
            );
            $data['dataBlocks'][] = [
                'title'     => $this->translator->trans('oro.sales.customers.opportunities.grid.label'),
                'priority'  => (int)$priority,
                'subblocks' => [['data' => [$opportunitiesData]]]
            ];
            $event->setData($data);
        }
    }

    /**
     * @param $targetClass
     *
     * @return int
     */
    protected function getBlockPriority($targetClass)
    {
        $config   = $this->configProvider->getConfig($targetClass);
        $priority = $config->get('associated_opportunity_block_priority');
        if (is_int($priority)) {
            return $priority;
        }

        return self::DEFAULT_GRID_BLOCK_PRIORITY;
    }
}
