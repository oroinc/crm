<?php

namespace Oro\Bundle\SalesBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AccountCreationStrategyProviderPass implements CompilerPassInterface
{
    const CHAIN_SERVICE     = 'oro_sales.provider.customer.account_creation.chain';
    const PROVIDER_TAG_NAME = 'oro_sales.provider.customer.account_creation';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $chainServiceDef = $container->findDefinition(self::CHAIN_SERVICE);

        if (null !== $chainServiceDef) {
            // find services
            $services = [];
            $taggedServices = $container->findTaggedServiceIds(self::PROVIDER_TAG_NAME);
            foreach ($taggedServices as $id => $attributes) {
                $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
                $services[$priority][] = new Reference($id);
            }
            if (empty($services)) {
                return;
            }

            // sort by priority and flatten
            krsort($services);
            $services = call_user_func_array('array_merge', $services);

            // register
            foreach ($services as $service) {
                $chainServiceDef->addMethodCall('addProvider', [$service]);
            }
        }
    }
}
