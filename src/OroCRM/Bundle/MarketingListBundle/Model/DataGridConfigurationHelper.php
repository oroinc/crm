<?php

namespace OroCRM\Bundle\MarketingListBundle\Model;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Provider\ConfigurationProviderInterface;
use Oro\Bundle\UIBundle\Tools\ArrayUtils;

class DataGridConfigurationHelper
{
    const ROOT_ALIAS_PLACEHOLDER = '__root_entity__';

    /**
     * @var ConfigurationProviderInterface
     */
    protected $configurationProvider;

    /**
     * @var array
     */
    protected $pathsToFix = [
        '[columns]',
        '[sorters][columns]',
        '[filters][columns]',
        '[source][query]'
    ];

    /**
     * @param ConfigurationProviderInterface $configurationProvider
     */
    public function __construct(ConfigurationProviderInterface $configurationProvider)
    {
        $this->configurationProvider = $configurationProvider;
    }

    /**
     * @param DatagridConfiguration $configuration
     * @param string                $gridName
     *
     * @return DatagridConfiguration
     */
    public function extendConfiguration(DatagridConfiguration $configuration, $gridName)
    {
        $gridConfiguration = $this->configurationProvider->getConfiguration($gridName);
        $basicAlias = $configuration->offsetGetByPath('[source][query][from][0][alias]');
        foreach ($this->pathsToFix as $path) {
            $forFix = $gridConfiguration->offsetGetByPath($path);
            if ($forFix) {
                $gridConfiguration->offsetSetByPath(
                    $path,
                    $this->fixMixinAlias($basicAlias, $forFix)
                );
            }
        }

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

    /**
     * @param string $alias
     * @param mixed $configuration
     * @return array|mixed
     */
    protected function fixMixinAlias($alias, $configuration)
    {
        if (is_array($configuration)) {
            foreach ($configuration as $key => $value) {
                $configuration[$key] = $this->fixMixinAlias($alias, $configuration[$key]);
            }
        } elseif (is_string($configuration)) {
            $configuration = str_replace(self::ROOT_ALIAS_PLACEHOLDER, $alias, $configuration);
        }

        return $configuration;
    }
}
