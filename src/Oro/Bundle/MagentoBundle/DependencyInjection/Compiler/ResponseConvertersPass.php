<?php

namespace Oro\Bundle\MagentoBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ResponseConvertersPass implements CompilerPassInterface
{
    const TAG = 'oro_magento.rest_response.converter';
    const PROVIDER_SERVICE_ID = 'oro_magento.converter.rest.response_converter_manager';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::PROVIDER_SERVICE_ID)) {
            return;
        }
        $serviceDef = $container->getDefinition(self::PROVIDER_SERVICE_ID);

        // find converters
        $taggedServices = $container->findTaggedServiceIds(self::TAG);
        foreach ($taggedServices as $id => $attributes) {
            $serviceDef->addMethodCall('addConverter', [$attributes[0]['type'], new Reference($id)]);
        }
    }
}
