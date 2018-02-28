<?php

namespace Oro\Bundle\ActivityContactBundle;

use Oro\Bundle\ActivityContactBundle\DependencyInjection\Compiler\DirectionProviderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroActivityContactBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DirectionProviderPass());
    }
}
