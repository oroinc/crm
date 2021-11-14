<?php

namespace Oro\Bundle\AccountBundle\Tests\Unit\ImportExport\Configuration;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AccountBundle\ImportExport\Configuration\AccountImportExportConfigurationProvider;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfiguration;

class AccountImportExportConfigurationProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGet()
    {
        self::assertEquals(
            new ImportExportConfiguration([
                ImportExportConfiguration::FIELD_ENTITY_CLASS => Account::class,
                ImportExportConfiguration::FIELD_EXPORT_PROCESSOR_ALIAS => 'oro_account',
                ImportExportConfiguration::FIELD_EXPORT_TEMPLATE_PROCESSOR_ALIAS => 'oro_account',
                ImportExportConfiguration::FIELD_IMPORT_PROCESSOR_ALIAS => 'oro_account.add_or_replace',
            ]),
            (new AccountImportExportConfigurationProvider())->get()
        );
    }
}
