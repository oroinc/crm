<?php

namespace Oro\Bundle\AnalyticsBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RFMBuilderPass implements CompilerPassInterface
{
    const RFM_BUILDER_SERVICE = 'oro_analytics.builder.rfm';
    const TAG = 'oro_analytics.builder.rfm';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::RFM_BUILDER_SERVICE)) {
            return;
        }

        $taggedServices = $container->findTaggedServiceIds(self::TAG);
        if (empty($taggedServices)) {
            return;
        }

        $analyticsBuilderDefinition = $container->getDefinition(self::RFM_BUILDER_SERVICE);

        foreach (array_keys($taggedServices) as $id) {
            $analyticsBuilderDefinition->addMethodCall(
                'addProvider',
                [new Reference($id)]
            );
        }
    }
}
