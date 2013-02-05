<?php
namespace Oro\Bundle\DataFlowBundle\CompilerPass;

use Symfony\Component\DependencyInjection\Reference;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * CompilerPass
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
        if (!$container->hasDefinition('dataflow_connector.chain')) {
            return;
        }

        $definition = $container->getDefinition(
            'dataflow_connector.chain'
        );

        $taggedServices = $container->findTaggedServiceIds('dataflow_connector');

        // for each tagged service, call addConnector method on dataflow_connector.chain
        foreach ($taggedServices as $id => $attribute) {
            $definition->addMethodCall(
                'addConnector',
                array(new Reference($id))
            );
        }
    }
}
