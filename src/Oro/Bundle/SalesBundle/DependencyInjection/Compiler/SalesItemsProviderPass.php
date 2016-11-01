<?php

namespace Oro\Bundle\SalesBundle\DependencyInjection\Compiler;

use Oro\Bundle\EntityBundle\DependencyInjection\Compiler\AbstractProviderCompilerPass;

class SalesItemsProviderPass extends AbstractProviderCompilerPass
{
    /**
     * {@inheritdoc}
     */
    protected function getService()
    {
        return 'oro_sales.customers.sales_items_provider';
    }

    /**
     * {@inheritdoc}
     */
    protected function getTag()
    {
        return 'oro_sales.customers.sales_items_provider';
    }
}
