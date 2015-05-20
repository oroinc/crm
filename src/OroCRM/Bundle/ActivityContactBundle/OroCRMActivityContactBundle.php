<?php

namespace OroCRM\Bundle\ActivityContactBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use OroCRM\Bundle\ActivityContactBundle\DependencyInjection\Compiler\DirectionProviderPass;

class OroCRMActivityContactBundle extends Bundle
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
