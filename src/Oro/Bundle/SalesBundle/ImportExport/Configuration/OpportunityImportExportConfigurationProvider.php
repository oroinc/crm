<?php

namespace Oro\Bundle\SalesBundle\ImportExport\Configuration;

use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfiguration;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfigurationInterface;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfigurationProviderInterface;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

/**
 * Provides import/export configuration for opportunity entities, defining field mappings and import/export behavior.
 */
class OpportunityImportExportConfigurationProvider implements ImportExportConfigurationProviderInterface
{
    #[\Override]
    public function get(): ImportExportConfigurationInterface
    {
        return new ImportExportConfiguration([
            ImportExportConfiguration::FIELD_ENTITY_CLASS => Opportunity::class,
            ImportExportConfiguration::FIELD_EXPORT_PROCESSOR_ALIAS => 'oro_sales_opportunity',
            ImportExportConfiguration::FIELD_EXPORT_TEMPLATE_PROCESSOR_ALIAS => 'oro_sales_opportunity',
            ImportExportConfiguration::FIELD_IMPORT_PROCESSOR_ALIAS => 'oro_sales_opportunity.add_or_replace',
        ]);
    }
}
