<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\SalesBundle\DependencyInjection\OroSalesExtension;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;

class OroSalesExtensionTest extends ExtensionTestCase
{
    public function testExtension()
    {
        $extension = new OroSalesExtension();

        $this->loadExtension($extension);

        $expectedDefinitions = [
            'oro_sales.importexport.configuration_provider.lead',
            'oro_sales.importexport.configuration_provider.b2b_customer',
            'oro_sales.importexport.configuration_provider.opportunity',
        ];
        $this->assertDefinitionsLoaded($expectedDefinitions);
    }
}
