<?php

namespace Oro\Bundle\ContactBundle\DependencyInjection\Compiler;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Adds Contact entity to the phone provider service.
 */
class PhoneProviderConfigPass implements CompilerPassInterface
{
    private const SERVICE_KEY = 'oro_address.provider.phone';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::SERVICE_KEY)) {
            return;
        }

        $container->getDefinition(self::SERVICE_KEY)
            ->addMethodCall('addTargetEntity', [Contact::class]);
    }
}
