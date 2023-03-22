<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\Repository\ContactRepository;
use Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData;
use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\ImportExportBundle\Async\Topic\ImportTopic;
use Oro\Bundle\ImportExportBundle\Async\Topic\PreImportTopic;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfiguration;
use Oro\Bundle\ImportExportBundle\Tests\Functional\AbstractImportExportTestCase;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

/**
 * @dbIsolationPerTest
 */
class ImportExportTest extends AbstractImportExportTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([LoadContactEntitiesData::class]);
    }

    public function testExportTemplate(): void
    {
        $this->assertExportTemplateWorks(
            $this->getExportImportConfiguration(),
            $this->getFullPathToDataFile($this->getExportTemplateFileName())
        );
    }

    public function testExport(): void
    {
        $this->assertExportWorks(
            $this->getExportImportConfiguration(),
            $this->getFullPathToDataFile('export.csv'),
            [
                'Id',
                'Organization Name',
                'Owner Username'
            ]
        );
    }

    public function testImportRecordWithAddStrategy(): void
    {
        $this->assertImportWorks(
            $this->getExportImportConfiguration('oro_contact.add'),
            $this->getFullPathToDataFile('import_one_record.csv')
        );

        self::assertCount(5, $this->getContactRepository()->findAll());
    }

    public function testImportRecordWithAddOrReplaceStrategy(): void
    {
        $this->assertImportWorks(
            $this->getExportImportConfiguration(),
            $this->getFullPathToDataFile('import_one_record.csv')
        );

        self::assertCount(5, $this->getContactRepository()->findAll());
    }


    public function testImportDuplicatedAddressPhoneWithAddStrategy(): void
    {
        $this->assertImportWorks(
            $this->getExportImportConfiguration('oro_contact.add'),
            $this->getFullPathToDataFile('contact_with_duplicate_address_phone.csv')
        );

        self::assertCount(8, $this->getContactRepository()->findAll());
    }

    public function testImportDuplicatedAddressPhoneWithAddOrReplaceStrategy(): void
    {
        $this->assertImportWorks(
            $this->getExportImportConfiguration(),
            $this->getFullPathToDataFile('contact_with_duplicate_address_phone.csv')
        );

        $results = $this->getContactRepository()->findAll();
        self::assertCount(8, $results);
        foreach ($results as $contact) {
            if (!in_array($contact->getFirstName(), ['Andrea', 'Matteo', 'Roberto', 'Stefano'])) {
                continue;
            }

            self::assertFalse($contact->getPhones()->isEmpty());
            self::assertFalse($contact->getAddresses()->isEmpty());
        }
    }

    public function testImportDuplicateRecord(): void
    {
        $this->markTestSkipped('Unskip after BAP-16301');

        $this->assertImportWorks(
            $this->getExportImportConfiguration(),
            $this->getFullPathToDataFile('contact_with_duplicate_records.csv')
        );

        self::assertCount(5, $this->getContactRepository()->findAll());
    }

    public function testUpdateIfNoneEmptyStrategyOnLastName(): void
    {
        $configuration = $this->getExportImportConfiguration();
        $importFilePath = $this->getFullPathToDataFile('update_name_prefix.csv');

        $this->assertPreImportActionExecuted($configuration, $importFilePath);
        $preImportMessageData = $this->getOneSentMessageWithTopic(PreImportTopic::getName());
        $this->assertMessageProcessorExecuted();

        $configManager = $this->getConfigManager();
        $importExportFieldConfig = $configManager
            ->getFieldConfig('importexport', Contact::class, 'lastName');
        $importExportFieldConfig->set('identity', FieldHelper::IDENTITY_ONLY_WHEN_NOT_EMPTY);
        $configManager->flush();

        self::assertMessageSent(ImportTopic::getName());
        $importMessageData = $this->getOneSentMessageWithTopic(ImportTopic::getName());
        $this->assertMessageProcessorExecuted();

        $this->assertTmpFileRemoved($preImportMessageData['fileName']);
        $this->assertTmpFileRemoved($importMessageData['fileName']);

        self::assertCount(4, $this->getContactRepository()->findAll());

        /** @var Contact $updatedContact */
        $updatedContact = $this->getReference('Contact_' . LoadContactEntitiesData::FIRST_ENTITY_NAME);
        $this->assertNotEmpty($updatedContact->getLastName());
        $this->assertSame('Ms.', $updatedContact->getNamePrefix());

        /**
         * Assert that update not clear snapshot field
         */
        $this->assertNotEmpty(
            $updatedContact->getTestMultiEnumSnapshot(),
            'Update through the import-export functionality must not clear the system fields'
        );
    }

    public function testImportValidate(): void
    {
        $this->assertImportValidateWorks(
            $this->getExportImportConfiguration(),
            $this->getFullPathToDataFile('import_wrong_data.csv'),
            $this->getFullPathToDataFile('import_validation_errors.json')
        );
    }

    private function getContactRepository(): ContactRepository
    {
        return self::getContainer()->get('doctrine')->getRepository(Contact::class);
    }

    private function getConfigManager(): ConfigManager
    {
        return self::getContainer()->get('oro_entity_config.config_manager');
    }

    private function getFullPathToDataFile(string $fileName): string
    {
        $dataDir = $this->getContainer()
            ->get('kernel')
            ->locateResource('@OroContactBundle/Tests/Functional/DataFixtures/Data');

        return $dataDir . DIRECTORY_SEPARATOR . $fileName;
    }

    private function getExportTemplateFileName(): string
    {
        $organizationRepository = $this->getContainer()->get('doctrine')->getRepository(Organization::class);

        /** @var Organization $organization */
        $organization = $organizationRepository->getFirst();

        return sprintf(
            'export_template_with_%s_org.csv',
            strtolower($organization->getName())
        );
    }

    private function getExportImportConfiguration(
        string $fieldImportProcessorAlias = 'oro_contact.add_or_replace'
    ): ImportExportConfiguration {
        return new ImportExportConfiguration([
            ImportExportConfiguration::FIELD_ENTITY_CLASS => Contact::class,
            ImportExportConfiguration::FIELD_EXPORT_PROCESSOR_ALIAS => 'oro_contact',
            ImportExportConfiguration::FIELD_EXPORT_TEMPLATE_PROCESSOR_ALIAS => 'oro_contact',
            ImportExportConfiguration::FIELD_IMPORT_PROCESSOR_ALIAS => $fieldImportProcessorAlias
        ]);
    }
}
