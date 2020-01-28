<?php

namespace Oro\Bundle\SalesBundle;

use Oro\Bundle\SalesBundle\DependencyInjection\Compiler\AccountAutocompleteProviderPass;
use Oro\Bundle\SalesBundle\DependencyInjection\Compiler\AccountCreationStrategyProviderPass;
use Oro\Bundle\SalesBundle\DependencyInjection\Compiler\AddProbabilityFieldToIgnoreForScalarDenormalizationPass;
use Oro\Bundle\SalesBundle\DependencyInjection\Compiler\CustomerIconProviderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * The SalesBundle bundle class.
 */
class OroSalesBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new AddProbabilityFieldToIgnoreForScalarDenormalizationPass());
        $container->addCompilerPass(new CustomerIconProviderPass());
        $container->addCompilerPass(new AccountCreationStrategyProviderPass());
        $container->addCompilerPass(new AccountAutocompleteProviderPass());
    }
}
