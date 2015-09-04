<?php

namespace OroCRM\Bundle\MagentoBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class BigNumberPass implements CompilerPassInterface
{
    const TAG = 'orocrm_magento.big_numbers_provider';
    const PROVIDER_SERVICE_ID = 'orocrm_magento.provider.big_number';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $serviceDef = $container->getDefinition(static::PROVIDER_SERVICE_ID);

        $taggedServices = $container->findTaggedServiceIds(self::TAG);
        $taggedServiceIds = array_keys($taggedServices);
        foreach ($taggedServiceIds as $id) {
            $serviceDef->addMethodCall('addValueProvider', [new Reference($id)]);
        }
    }
}
