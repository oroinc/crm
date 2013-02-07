<?php
namespace Oro\Bundle\DataFlowBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * CompilerPass Connector
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class ConnectorCompilerPass implements CompilerPassInterface
{

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('oro_dataflow.connectors')) {
            return;
        }

        // get registry
        $definition = $container->getDefinition('oro_dataflow.connectors');

        // get jobs
        $taggedServices = $container->findTaggedServiceIds('oro_dataflow_job');

        // for each tagged service, call addConnector method on dataflow.connectors
        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                // retrieve reference to relevant connector and add job into
                if (!$container->hasDefinition($attributes['connector'])) {
                    throw new InvalidArgumentException(sprintf('The connector service definition "%s" does not exist.', $attributes['connector']));
                }
                $definition->addMethodCall(
                    'addToConnector',
                    array(new Reference($attributes['connector']), new Reference($id))
                );
            }
        }
    }
}
