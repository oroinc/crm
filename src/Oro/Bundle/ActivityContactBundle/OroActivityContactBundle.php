<?php

namespace Oro\Bundle\ActivityContactBundle;

use Oro\Component\DependencyInjection\Compiler\PriorityNamedTaggedServiceCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * The ActivityContactBundle bundle class.
 */
class OroActivityContactBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new PriorityNamedTaggedServiceCompilerPass(
            'oro_activity_contact.provider',
            'oro_activity_direction.provider',
            'class'
        ));
    }
}
