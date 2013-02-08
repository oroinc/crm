<?php
namespace Oro\Bundle\DataFlowBundle\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * Define DBAL configuration
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class DbalConfiguration extends AbstractConfiguration
{

    /**
     * Label of root node configuration
     * @staticvar string
     */
    const ROOT_NODE = 'database';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(static::ROOT_NODE);

        $rootNode
            ->children()
                ->enumNode('driver')
                    ->cannotBeEmpty()
                    ->isRequired()
                    ->values(array('pdo_mysql', 'pdo_sqlite', 'pdo_pgsql', 'pdo_oci', 'oci8', 'pdo_sqlsrv'))
                ->end()

                ->scalarNode('username')
                    ->cannotBeEmpty()
                    ->isRequired()
                ->end()

                ->scalarNode('password')->isRequired()->end()

                ->scalarNode('host')->end()

                ->scalarNode('dbname')->end()

                ->scalarNode('port')->end()

                ->scalarNode('unix_socket')->end()

                ->scalarNode('charset')->end()

                ->scalarNode('path')->end()

                ->booleanNode('memory')->end()

                ->end()
            ->end();

        return $treeBuilder;
    }

}
