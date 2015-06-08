<?php

namespace OroCRM\Bundle\ActivityContactBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DirectionProviderPass implements CompilerPassInterface
{
    const TAG                 = 'orocrm_activity_direction.provider';
    const PROVIDER_SERVICE_ID = 'orocrm_activity_contact.provider';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::PROVIDER_SERVICE_ID)) {
            return;
        }

        // find providers
        $providers      = [];
        $taggedServices = $container->findTaggedServiceIds(self::TAG);
        foreach ($taggedServices as $id => $attributes) {
            $priority               = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $providers[$priority][] = new Reference($id);
        }
        if (empty($providers)) {
            return;
        }

        // sort by priority and flatten
        ksort($providers);
        $providers = call_user_func_array('array_merge', $providers);

        // register
        $serviceDef = $container->getDefinition(self::PROVIDER_SERVICE_ID);
        foreach ($providers as $provider) {
            $serviceDef->addMethodCall('addProvider', [$provider]);
        }
    }
}
