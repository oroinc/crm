<?php

namespace OroCRM\Bundle\MarketingListBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Provider\ConfigurationProviderInterface;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use OroCRM\Bundle\MarketingListBundle\Model\MarketingListHelper;

class ConfigurationProvider implements ConfigurationProviderInterface
{
    const GRID_PREFIX = 'orocrm_marketing_list_items_grid_';
    const GRID_NAME_OFFSET = '[name]';

    /**
     * @var ConfigurationProviderInterface
     */
    protected $chainConfigurationProvider;

    /**
     * @var ConfigProvider
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
     * @param ConfigProvider                 $configProvider
     * @param MarketingListHelper            $helper
     */
    public function __construct(
        ConfigurationProviderInterface $chainConfigurationProvider,
        ConfigProvider $configProvider,
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
        return (bool)$this->helper->getMarketingListIdByGridName($gridName);
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
            if (!$marketingList) {
                throw new \RuntimeException(
                    sprintf('Marketing List with id "%s" not found.', $marketingListId)
                );
            }

            // Get configuration based on marketing list type
            if ($marketingList->isManual()) {
                $concreteGridName = $this->getEntityGridName($marketingList->getEntity());
            } else {
                $postfix = str_replace(self::GRID_PREFIX . $marketingList->getId(), '', $gridName);
                $concreteGridName = Segment::GRID_PREFIX . $marketingList->getSegment()->getId() . $postfix;
            }

            $concreteGridConfiguration =  $this->chainConfigurationProvider->getConfiguration($concreteGridName);
            // Reset configured name to current gridName for further usage in Listener and Extension
            $concreteGridConfiguration->offsetSetByPath(self::GRID_NAME_OFFSET, $gridName);
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
        $gridName = null;
        if ($this->configProvider->hasConfig($entityName)) {
            $config = $this->configProvider->getConfig($entityName);
            $gridName = $config->get('grid_name');
        }

        if (!$gridName) {
            throw new \RuntimeException(sprintf('Grid not found for entity "%s"', $entityName));
        }

        return $gridName;
    }
}
