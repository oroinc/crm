<?php

namespace OroCRM\Bundle\ContactBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PhoneHolderHelperConfigPass implements CompilerPassInterface
{
    const SERVICE_KEY = 'oro_address.phone_holder_helper';

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
