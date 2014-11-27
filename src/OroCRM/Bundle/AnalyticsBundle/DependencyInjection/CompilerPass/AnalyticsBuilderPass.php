<?php

namespace OroCRM\Bundle\AnalyticsBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AnalyticsBuilderPass implements CompilerPassInterface
{
    const ANALYTICS_BUILDER_SERVICE = 'orocrm_analytics.builder';
    const TAG = 'orocrm_analytics.builder';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::ANALYTICS_BUILDER_SERVICE)) {
            return;
        }

        $taggedServices = $container->findTaggedServiceIds(self::TAG);
        if (empty($taggedServices)) {
            return;
        }

        $analyticsBuilderDefinition = $container->getDefinition(self::ANALYTICS_BUILDER_SERVICE);

        foreach (array_keys($taggedServices) as $id) {
            $analyticsBuilderDefinition->addMethodCall(
                'addBuilder',
                [new Reference($id)]
            );
        }
    }
}
