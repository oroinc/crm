<?php

namespace Oro\Bundle\ActivityContactBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\ActivityContactBundle\DependencyInjection\Compiler\DirectionProviderPass;

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
