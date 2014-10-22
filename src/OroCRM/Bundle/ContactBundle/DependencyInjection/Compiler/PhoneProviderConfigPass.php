<?php

namespace OroCRM\Bundle\ContactBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PhoneProviderConfigPass implements CompilerPassInterface
{
    const SERVICE_KEY = 'oro_address.provider.phone';

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
            'addTargetEntity',
            [$container->getParameter('orocrm_contact.entity.class')]
        );
    }
}
