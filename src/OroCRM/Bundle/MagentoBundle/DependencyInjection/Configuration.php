<?php

namespace OroCRM\Bundle\MagentoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $root        = $treeBuilder->root('orocrm_magento');

        $soapTransportSettings = $root->children()->arrayNode('sync_settings')->addDefaultsIfNotSet();
        $soapTransportSettings->children()
            ->scalarNode('mistiming_assumption_interval')
            ->defaultValue('5 minutes')
            ->cannotBeEmpty()
            ->info(
                'There is possibility to have mistiming between web-nodes if Magento ' .
                'instance is deployed as a web farm in order to prevent loss of data sync ' .
                'process always include some additional time assumption. Configuration is in time relative format' .
                '(see: http://php.net/manual/en/datetime.formats.relative.php)'
            )
            ->example('10 minutes');

        return $treeBuilder;
    }
}
