<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\ImportExport\Configuration;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\ImportExport\Configuration\ContactImportExportConfigurationProvider;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfiguration;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactImportExportConfigurationProviderTest extends TestCase
{
    /**
     * @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $translator;

    /**
     * @var ContactImportExportConfigurationProvider
     */
    private $provider;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->provider = new ContactImportExportConfigurationProvider($this->translator);
    }

    public function testGet()
    {
        $this->translator
            ->expects(static::once())
            ->method('trans')
            ->withConsecutive(
                ['oro.contact.import.strategy.tooltip']
            )
            ->willReturnOnConsecutiveCalls(
                '1'
            );

        static::assertEquals(
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
