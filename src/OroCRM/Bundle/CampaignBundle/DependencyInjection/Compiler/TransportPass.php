<?php

namespace OroCRM\Bundle\CampaignBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class TransportPass implements CompilerPassInterface
{
    const TAG = 'orocrm_campaign.email_transport';
    const SERVICE = 'orocrm_campaign.email_transport.provider';

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
        foreach ($taggedServices as $id => $attributes) {
            $alias = null;
            foreach ($attributes as $attribute) {
                if (array_key_exists('alias', $attribute)) {
                    $alias = !empty($attribute['alias']);
                    break;
                }
            }
            $contentProviderManagerDefinition->addMethodCall(
                'addTransport',
                array(new Reference($id), $alias)
            );
        }
    }
}
