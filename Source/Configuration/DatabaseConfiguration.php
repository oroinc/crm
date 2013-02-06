<?php
namespace Oro\Bundle\DataFlowBundle\Source\Configuration;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

/**
 * Database configuration
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class DatabaseConfiguration implements ConfigurationInterface
{

    /**
     * Processes configuration
     * @param \ArrayAccess $configuration
     */
    public function process($configuration)
    {
        $processor = new Processor();
        $configuration = $processor->processConfiguration($this, $configuration);

        return $configuration;
    }

    /**
     * {@inheritDoc}
     * Inspired by Doctrine\Bundle\DoctrineBundle\DependencyInjection\Configuration
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('configuration');
        $rootNode
            ->children()
                ->scalarNode('driver')->defaultValue('pdo_mysql')->end()
                ->scalarNode('host')->defaultValue('localhost')->end()
                ->scalarNode('port')->defaultNull()->end()
                ->scalarNode('dbname')->end()
                ->scalarNode('user')->defaultValue('root')->end()
                ->scalarNode('password')->defaultNull()->end()
                ->scalarNode('charset')->defaultValue('UTF8')->end()
            ->end()
        ->end();

        return $treeBuilder;
    }

}
