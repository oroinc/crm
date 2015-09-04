<?php

namespace OroCRM\Bundle\MagentoBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use OroCRM\Bundle\MagentoBundle\DependencyInjection\Compiler\BigNumberPass;

class OroCRMMagentoBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new BigNumberPass());
    }
}
