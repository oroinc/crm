<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\ImportExport\Configuration;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\ImportExport\Configuration\ContactImportExportConfigurationProvider;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfiguration;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactImportExportConfigurationProviderTest extends TestCase
{
    private TranslatorInterface&MockObject $translator;
    private ContactImportExportConfigurationProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->provider = new ContactImportExportConfigurationProvider($this->translator);
    }

    public function testGet(): void
    {
        $this->translator->expects(self::once())
            ->method('trans')
            ->with('oro.contact.import.strategy.tooltip')
            ->willReturn('1');

        self::assertEquals(
            new ImportExportConfiguration([
                ImportExportConfiguration::FIELD_ENTITY_CLASS => Contact::class,
                ImportExportConfiguration::FIELD_EXPORT_PROCESSOR_ALIAS => 'oro_contact',
                ImportExportConfiguration::FIELD_EXPORT_TEMPLATE_PROCESSOR_ALIAS => 'oro_contact',
                ImportExportConfiguration::FIELD_IMPORT_PROCESSOR_ALIAS => 'oro_contact.add_or_replace',
                ImportExportConfiguration::FIELD_IMPORT_STRATEGY_TOOLTIP => '1'
            ]),
            $this->provider->get()
        );
    }
}
