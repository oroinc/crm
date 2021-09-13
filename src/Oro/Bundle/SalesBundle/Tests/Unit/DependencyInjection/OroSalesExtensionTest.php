<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\SalesBundle\Controller\Api\Rest as Api;
use Oro\Bundle\SalesBundle\DependencyInjection\OroSalesExtension;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;

class OroSalesExtensionTest extends ExtensionTestCase
{
    public function testExtension(): void
    {
        $extension = new OroSalesExtension();

        $this->loadExtension($extension);

        $expectedDefinitions = [
            'oro_sales.importexport.configuration_provider.lead',
            'oro_sales.importexport.configuration_provider.b2b_customer',
            'oro_sales.importexport.configuration_provider.opportunity',
            Api\B2bCustomerController::class,
            Api\B2bCustomerEmailController::class,
            Api\B2bCustomerPhoneController::class,
            Api\LeadAddressController::class,
            Api\LeadController::class,
            Api\LeadEmailController::class,
            Api\LeadPhoneController::class,
            Api\OpportunityController::class,
            Api\SalesFunnelController::class,
        ];
        $this->assertDefinitionsLoaded($expectedDefinitions);
    }
}
