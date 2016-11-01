<?php

namespace Oro\Bundle\SalesBundle;

use Oro\Bundle\SalesBundle\DependencyInjection\Compiler\SalesItemsProviderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroSalesBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new SalesItemsProviderPass());
    }
}
