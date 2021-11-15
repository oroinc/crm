<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\ImportExport\Configuration;

use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfiguration;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\ImportExport\Configuration\LeadImportExportConfigurationProvider;

class LeadImportExportConfigurationProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGet()
    {
        self::assertEquals(
            new ImportExportConfiguration([
                ImportExportConfiguration::FIELD_ENTITY_CLASS => Lead::class,
                ImportExportConfiguration::FIELD_EXPORT_PROCESSOR_ALIAS => 'oro_sales_lead',
                ImportExportConfiguration::FIELD_EXPORT_TEMPLATE_PROCESSOR_ALIAS => 'oro_sales_lead',
                ImportExportConfiguration::FIELD_IMPORT_PROCESSOR_ALIAS => 'oro_sales_lead.add_or_replace',
            ]),
            (new LeadImportExportConfigurationProvider())->get()
        );
    }
}
