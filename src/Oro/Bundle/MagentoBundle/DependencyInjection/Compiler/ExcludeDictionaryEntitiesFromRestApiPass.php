<?php

namespace Oro\Bundle\MagentoBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Excludes Magento related dictionary entities from old REST API.
 */
class ExcludeDictionaryEntitiesFromRestApiPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('oro_entity.routing.options_resolver.dictionary_entity')
            ->addMethodCall('addExclusion', ['Oro\Bundle\MagentoBundle\Entity\CartStatus'])
            ->addMethodCall('addExclusion', ['Extend\Entity\EV_Creditmemo_Status'])
            ->addMethodCall('addExclusion', ['Extend\Entity\EV_Mage_Subscr_Status']);
    }
}
