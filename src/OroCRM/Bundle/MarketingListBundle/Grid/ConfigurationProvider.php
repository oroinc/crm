<?php

namespace OroCRM\Bundle\MarketingListBundle\Grid;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Provider\ConfigurationProviderInterface;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProviderInterface;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListType;
use OroCRM\Bundle\MarketingListBundle\Model\MarketingListHelper;

class ConfigurationProvider implements ConfigurationProviderInterface
{
    const GRID_PREFIX = 'orocrm_marketing_list_items_grid_';

    /**
     * @var ConfigurationProviderInterface
     */
    protected $chainConfigurationProvider;

    /**
     * @var ConfigProviderInterface
     */
    protected $configProvider;

    /**
     * @var MarketingListHelper
     */
    protected $helper;

    /**
     * @var DatagridConfiguration[]
     */
    private $configuration = [];

    /**
     * @param ConfigurationProviderInterface $chainConfigurationProvider
     * @param ConfigProviderInterface $configProvider
     * @param MarketingListHelper $helper
     */
    public function __construct(
        ConfigurationProviderInterface $chainConfigurationProvider,
        ConfigProviderInterface $configProvider,
        MarketingListHelper $helper
    ) {
        $this->chainConfigurationProvider = $chainConfigurationProvider;
        $this->configProvider = $configProvider;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable($gridName)
    {
        return strpos($gridName, self::GRID_PREFIX) === 0;
    }

    /**
     * Get grid configuration based on marketing list type.
     *
     * Get segments or concrete entity grid configuration by marketing list type and entity.
     * This configuration will be used as marketing list items grid configuration.
     *
     * @param string $gridName
     * @return DatagridConfiguration
     */
    public function getConfiguration($gridName)
    {
        if (empty($this->configuration[$gridName])) {
            $marketingListId = $this->helper->getMarketingListIdByGridName($gridName);
            $marketingList = $this->helper->getMarketingList($marketingListId);

            // Get configuration based on marketing list type
            if ($marketingList->getType()->getName() === MarketingListType::TYPE_MANUAL) {
                $concreteGridName = $this->getEntityGridName($marketingList->getEntity());
            } else {
                $postfix = str_replace(self::GRID_PREFIX . $marketingList->getId(), '', $gridName);
                $concreteGridName = Segment::GRID_PREFIX . $marketingList->getSegment()->getId() . $postfix;
            }

            $concreteGridConfiguration =  $this->chainConfigurationProvider->getConfiguration($concreteGridName);
            // Reset configured name to current gridName for further usage in Listener and Extension
            $concreteGridConfiguration->offsetSetByPath('[name]', $gridName);
            $this->configuration[$gridName] = $concreteGridConfiguration;
        }

        return $this->configuration[$gridName];
    }

    /**
     * @param string $entityName
     * @return string|null
     */
    protected function getEntityGridName($entityName)
    {
        if ($this->configProvider->hasConfig($entityName)) {
            $config = $this->configProvider->getConfig($entityName);
            return $config->get('grid_name');
        }

        return null;
    }
}
