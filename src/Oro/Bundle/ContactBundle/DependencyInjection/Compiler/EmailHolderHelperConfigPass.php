<?php

namespace Oro\Bundle\ContactBundle\DependencyInjection\Compiler;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Adds Contact entity to the email provider service.
 */
class EmailHolderHelperConfigPass implements CompilerPassInterface
{
    private const SERVICE_KEY = 'oro_email.email_holder_helper';

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
