<?php

namespace OroCRM\Bundle\MarketingListBundle\Model;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Provider\ConfigurationProviderInterface;

class DataGridConfigurationHelper
{
    /**
     * @var ConfigurationProviderInterface
     */
    protected $configurationProvider;

    /**
     * @param ConfigurationProviderInterface $configurationProvider
     */
    public function __construct(ConfigurationProviderInterface $configurationProvider)
    {
        $this->configurationProvider = $configurationProvider;
    }

    /**
     * @param DatagridConfiguration $configuration
     * @param string $gridName
     * @return DatagridConfiguration
     */
    public function extendConfiguration(DatagridConfiguration $configuration, $gridName)
    {
        $gridConfiguration = $this->configurationProvider->getConfiguration($gridName);
        $scopes = array_diff(array_keys($gridConfiguration->getIterator()->getArrayCopy()), ['name']);

        foreach ($scopes as $scope) {
            $path = sprintf('[%s]', $scope);
            $additionalParams = $gridConfiguration->offsetGetByPath($path);

            if (empty($additionalParams)) {
                continue;
            }

            $baseParams = $configuration->offsetGetByPath($path, array());
            $configuration->offsetSetByPath($path, array_merge_recursive($baseParams, $additionalParams));
        }

        return $configuration;
    }
}
