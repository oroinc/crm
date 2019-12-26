<?php

namespace Oro\Bundle\SalesBundle;

use Oro\Component\DependencyInjection\Compiler\PriorityTaggedServiceViaAddMethodCompilerPass;
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
        $container->addCompilerPass(new PriorityTaggedServiceViaAddMethodCompilerPass(
            'oro_sales.provider.customer.chain_customer_icon',
            'addProvider',
            'oro_sales.customer_icon'
        ));
        $container->addCompilerPass(new PriorityTaggedServiceViaAddMethodCompilerPass(
            'oro_sales.provider.customer.account_creation.chain',
            'addProvider',
            'oro_sales.provider.customer.account_creation'
        ));
        $container->addCompilerPass(new PriorityTaggedServiceViaAddMethodCompilerPass(
            'oro_sales.provider.customer.account_autocomplete.chain',
            'addProvider',
            'oro_sales.provider.customer.account_autocomplete'
        ));
    }
}
