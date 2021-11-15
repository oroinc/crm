<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\ImportExport\Configuration;

use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfiguration;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\ImportExport\Configuration\B2bCustomerImportExportConfigurationProvider;

class B2bCustomerImportExportConfigurationProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGet()
    {
        self::assertEquals(
            new ImportExportConfiguration([
                ImportExportConfiguration::FIELD_ENTITY_CLASS => B2bCustomer::class,
                ImportExportConfiguration::FIELD_EXPORT_PROCESSOR_ALIAS => 'oro_sales_b2bcustomer',
                ImportExportConfiguration::FIELD_EXPORT_TEMPLATE_PROCESSOR_ALIAS => 'oro_sales_b2bcustomer',
                ImportExportConfiguration::FIELD_IMPORT_PROCESSOR_ALIAS => 'oro_sales_b2bcustomer',
            ]),
            (new B2bCustomerImportExportConfigurationProvider())->get()
        );
    }
}
