<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\ImportExport\Configuration;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\ImportExport\Configuration\ContactImportExportConfigurationProvider;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfiguration;
use PHPUnit\Framework\TestCase;

class ContactImportExportConfigurationProviderTest extends TestCase
{
    public function testGet()
    {
        static::assertEquals(
            new ImportExportConfiguration([
                ImportExportConfiguration::FIELD_ENTITY_CLASS => Contact::class,
                ImportExportConfiguration::FIELD_EXPORT_PROCESSOR_ALIAS => 'oro_contact',
                ImportExportConfiguration::FIELD_EXPORT_TEMPLATE_PROCESSOR_ALIAS => 'oro_contact',
                ImportExportConfiguration::FIELD_IMPORT_PROCESSOR_ALIAS => 'oro_contact.add_or_replace',
            ]),
            (new ContactImportExportConfigurationProvider())->get()
        );
    }
}
