<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\ImportExport\Configuration;

use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfiguration;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\ImportExport\Configuration\OpportunityImportExportConfigurationProvider;

class OpportunityImportExportConfigurationProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGet()
    {
        self::assertEquals(
            new ImportExportConfiguration([
                ImportExportConfiguration::FIELD_ENTITY_CLASS => Opportunity::class,
                ImportExportConfiguration::FIELD_EXPORT_PROCESSOR_ALIAS => 'oro_sales_opportunity',
                ImportExportConfiguration::FIELD_EXPORT_TEMPLATE_PROCESSOR_ALIAS => 'oro_sales_opportunity',
                ImportExportConfiguration::FIELD_IMPORT_PROCESSOR_ALIAS => 'oro_sales_opportunity.add_or_replace',
            ]),
            (new OpportunityImportExportConfigurationProvider())->get()
        );
    }
}
