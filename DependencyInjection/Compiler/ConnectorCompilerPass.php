<?php
namespace Oro\Bundle\DataFlowBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

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

        $definition = $container->getDefinition('oro_dataflow.connectors');

        $taggedServices = $container->findTaggedServiceIds('oro_dataflow_connector');

        // for each tagged service, call addConnector method on dataflow.connectors
        foreach ($taggedServices as $id => $attribute) {
            $definition->addMethodCall(
                'addConnector',
                array(new Reference($id))
            );
        }
    }
}
