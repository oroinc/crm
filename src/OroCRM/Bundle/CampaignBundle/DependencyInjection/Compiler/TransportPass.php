<?php

namespace Oro\Bundle\CampaignBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class TransportPass implements CompilerPassInterface
{
    const TAG = 'oro_campaign.email_transport';
    const SERVICE = 'oro_campaign.email_transport.provider';

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::SERVICE)) {
            return;
        }

        $contentProviderManagerDefinition = $container->getDefinition(self::SERVICE);
        $taggedServices = $container->findTaggedServiceIds(self::TAG);
        foreach (array_keys($taggedServices) as $id) {
            $contentProviderManagerDefinition->addMethodCall(
                'addTransport',
                array(new Reference($id))
            );
        }
    }
}
