<?php

namespace Oro\Bundle\SalesBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\SalesBundle\DependencyInjection\Compiler\CustomerIconProviderPass;
use Oro\Bundle\SalesBundle\DependencyInjection\Compiler\AccountCreationStrategyProviderPass;
use Oro\Bundle\SalesBundle\DependencyInjection\Compiler\AccountAutocompleteProviderPass;

class OroSalesBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new CustomerIconProviderPass());
        $container->addCompilerPass(new AccountCreationStrategyProviderPass());
        $container->addCompilerPass(new AccountAutocompleteProviderPass());
    }
}
