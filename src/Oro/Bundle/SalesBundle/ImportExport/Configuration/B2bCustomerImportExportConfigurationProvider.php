<?php

namespace Oro\Bundle\SalesBundle\ImportExport\Configuration;

use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfiguration;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfigurationInterface;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfigurationProviderInterface;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;

class B2bCustomerImportExportConfigurationProvider implements ImportExportConfigurationProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function get(): ImportExportConfigurationInterface
    {
        return new ImportExportConfiguration([
            ImportExportConfiguration::FIELD_ENTITY_CLASS => B2bCustomer::class,
            ImportExportConfiguration::FIELD_EXPORT_PROCESSOR_ALIAS => 'oro_sales_b2bcustomer',
            ImportExportConfiguration::FIELD_EXPORT_TEMPLATE_PROCESSOR_ALIAS => 'oro_sales_b2bcustomer',
            ImportExportConfiguration::FIELD_IMPORT_PROCESSOR_ALIAS => 'oro_sales_b2bcustomer',
        ]);
    }
}
