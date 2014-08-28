<?php

namespace OroCRM\Bundle\MarketingListBundle\Model;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Provider\ConfigurationProviderInterface;
use Oro\Bundle\UIBundle\Tools\ArrayUtils;

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
            $baseParams = $configuration->offsetGetByPath($path, []);

            if (!is_array($additionalParams) || !is_array($baseParams)) {
                continue;
            }

            $configuration->offsetSetByPath(
                $path,
                ArrayUtils::arrayMergeRecursiveDistinct($baseParams, $additionalParams)
            );
        }

        return $configuration;
    }
}
