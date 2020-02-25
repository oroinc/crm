<?php

namespace Oro\Bundle\SalesBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Allows OpportunityProbabilitySubscriber process field Opportunity::probability instead of ScalarFieldDenormalizer
 */
class AddProbabilityFieldToIgnoreForScalarDenormalizationPass implements CompilerPassInterface
{
    const SERVICE_KEY = 'oro_importexport.serializer.scalar_field_denormalizer';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::SERVICE_KEY)) {
            return;
        }

        $serviceDef = $container->getDefinition(self::SERVICE_KEY);
        $serviceDef->addMethodCall(
            'addFieldToIgnore',
            [
                'Oro\Bundle\SalesBundle\Entity\Opportunity',
                'probability'
            ]
        );
    }
}
