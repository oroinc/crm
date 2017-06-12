<?php

namespace OroCRM\Bundle\MagentoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use OroCRM\Bundle\MagentoBundle\Exception\InvalidConfigurationException;

class Configuration implements ConfigurationInterface
{
    const DISCOVERY_NODE = 'account_discovery';
    const DISCOVERY_MATCH_KEY = 'match';
    const DISCOVERY_MATCH_LATEST = 'latest';
    const DISCOVERY_MATCH_FIRST = 'first';
    const DISCOVERY_EMPTY_KEY = 'empty';
    const DISCOVERY_OPTIONS_KEY = 'options';
    const DISCOVERY_STRATEGY_KEY = 'strategy';
    const DISCOVERY_FIELDS_KEY = 'fields';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $root = $treeBuilder->root('oro_crm_magento');

        $root
            ->children()
                ->arrayNode('sync_settings')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('mistiming_assumption_interval')
                            ->defaultValue('5 minutes')
                            ->cannotBeEmpty()
                            ->info(
                                'There is possibility to have mistiming between web-nodes if Magento ' .
                                'instance is deployed as a web farm in order to prevent loss of data sync ' .
                                'process always include some additional time assumption. ' .
                                'Configuration is in time relative' .
                                'format (see: http://php.net/manual/en/datetime.formats.relative.php)'
                            )
                            ->example('10 minutes')
                        ->end()
                        ->scalarNode('initial_import_step_interval')
                            ->defaultValue('7 days')
                            ->cannotBeEmpty()
                            ->info(
                                'This interval will be used in initial sync, ' .
                                'connector will walk starting from now or' .
                                'last initial import date and will import data from now till ' .
                                'previous date by step interval.' .
                                'Should be \DateInterval::createFromDateString argument value'
                            )
                            ->example('14 days')
                        ->end()
                        ->scalarNode('region_sync_interval')
                            ->defaultValue('1 day')
                            ->cannotBeEmpty()
                            ->info('This interval will be used to sync regions')
                            ->example('14 days')
                        ->end()
                        ->booleanNode('skip_ssl_verification')
                            ->defaultValue(false)
                            ->info(
                                'Enabling this options will turn off SSL certificate validation and allow self ' .
                                'signed certificates'
                            )
                        ->end()
                    ->end()
                ->end()
                ->arrayNode(self::DISCOVERY_NODE)
                    ->performNoDeepMerging()
                    ->children()
                        ->arrayNode(self::DISCOVERY_FIELDS_KEY)
                            ->prototype('variable')
                            ->end()
                        ->end()
                        ->arrayNode(self::DISCOVERY_STRATEGY_KEY)
                            ->prototype('variable')
                            ->end()
                        ->end()
                        ->arrayNode(self::DISCOVERY_OPTIONS_KEY)
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode(self::DISCOVERY_EMPTY_KEY)
                                    ->defaultFalse()
                                ->end()
                                ->enumNode(self::DISCOVERY_MATCH_KEY)
                                    ->values([self::DISCOVERY_MATCH_LATEST, self::DISCOVERY_MATCH_FIRST])
                                    ->defaultValue(self::DISCOVERY_MATCH_LATEST)
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->validate()
                        ->always(
                            function ($value) {
                                if (!empty($value['strategy'])) {
                                    $strategyFields = array_keys($value['strategy']);
                                    $fields =  array_keys($value['fields']);
                                    $unknownFields = array_diff($strategyFields, $fields);
                                    if (count($unknownFields) > 0) {
                                        throw new InvalidConfigurationException(
                                            sprintf(
                                                'Strategy configuration contains unknown fields "%s"',
                                                implode(', ', $unknownFields)
                                            )
                                        );
                                    }
                                }

                                return $value;
                            }
                        )
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
