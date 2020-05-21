<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\Repository\ContactRepository;
use Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData;
use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
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
        $this->loadFixtures(
            [
                LoadContactEntitiesData::class,
            ]
        );
    }

    public function testExportTemplate()
    {
        $exportTemplateFileName = $this->getExportTemplateFileName();

        $this->assertExportTemplateWorks(
            $this->getExportImportConfiguration(),
            $this->getFullPathToDataFile($exportTemplateFileName)
        );
    }

    public function testExport()
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

    public function testImportRecordWithAddStrategy()
    {
        $this->assertImportWorks(
            $this->getExportImportConfiguration('oro_contact.add'),
            $this->getFullPathToDataFile('import_one_record.csv')
        );

        static::assertCount(
            5,
            $this->getContactRepository()->findAll()
        );
    }

    public function testImportRecordWithAddOrReplaceStrategy()
    {
        $this->assertImportWorks(
            $this->getExportImportConfiguration(),
            $this->getFullPathToDataFile('import_one_record.csv')
        );

        static::assertCount(
            5,
            $this->getContactRepository()->findAll()
        );
    }

    public function testImportDuplicateRecord()
    {
        $this->markTestSkipped("Unskip after BAP-16301");

        $this->assertImportWorks(
            $this->getExportImportConfiguration(),
            $this->getFullPathToDataFile('contact_with_duplicate_records.csv')
        );

        static::assertCount(
            5,
            $this->getContactRepository()->findAll()
        );
    }

    public function testUpdateIfNoneEmptyStrategyOnLastName()
    {
        $configManager = $this->getConfigManager();
        $importExportFieldConfig = $configManager
            ->getFieldConfig('importexport', Contact::class, 'lastName');
        $importExportFieldConfig->set('identity', FieldHelper::IDENTITY_ONLY_WHEN_NOT_EMPTY);
        $configManager->flush();

        $this->assertImportWorks(
            $this->getExportImportConfiguration(),
            $this->getFullPathToDataFile('update_name_prefix.csv')
        );

        static::assertCount(
            4,
            $this->getContactRepository()->findAll()
        );

        /**
         * @var $updatedContact Contact
         */
        $updatedContact = $this->getReference('Contact_' . LoadContactEntitiesData::FIRST_ENTITY_NAME);
        $this->assertNotEmpty($updatedContact->getLastName());
        $this->assertSame('Ms.', $updatedContact->getNamePrefix());

        /**
         * Assert that update not clear snapshot field
         */
        $this->assertNotEmpty(
            $updatedContact->getTestMultiEnumSnapshot(),
            "Update through the import-export functionality mustn't clear the system fields !"
        );
    }

    public function testImportValidate()
    {
        $this->assertImportValidateWorks(
            $this->getExportImportConfiguration(),
            $this->getFullPathToDataFile('import_wrong_data.csv'),
            $this->getFullPathToDataFile('import_validation_errors.json')
        );
    }

    /**
     * @return ContactRepository
     */
    private function getContactRepository()
    {
        return static::getContainer()
            ->get('doctrine')
            ->getManagerForClass(Contact::class)
            ->getRepository(Contact::class);
    }

    /**
     * @return ConfigManager
     */
    private function getConfigManager()
    {
        return $config = $this
            ->getContainer()
            ->get('oro_entity_config.config_manager');
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    private function getFullPathToDataFile($fileName)
    {
        $dataDir = $this->getContainer()
            ->get('kernel')
            ->locateResource('@OroContactBundle/Tests/Functional/DataFixtures/Data');

        return $dataDir . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * @return string
     */
    private function getExportTemplateFileName()
    {
        $organizationRepository = $this
            ->getContainer()
            ->get('doctrine')
            ->getManagerForClass(Organization::class)
            ->getRepository(Organization::class);

        /**
         * @var $organization Organization
         */
        $organization = $organizationRepository->getFirst();

        return sprintf(
            'export_template_with_%s_org.csv',
            strtolower($organization->getName())
        );
    }

    /**
     * @param string $fieldImportProcessorAlias
     *
     * @return ImportExportConfiguration
     */
    private function getExportImportConfiguration($fieldImportProcessorAlias = 'oro_contact.add_or_replace')
    {
        return new ImportExportConfiguration([
            ImportExportConfiguration::FIELD_ENTITY_CLASS => Contact::class,
            ImportExportConfiguration::FIELD_EXPORT_PROCESSOR_ALIAS => 'oro_contact',
            ImportExportConfiguration::FIELD_EXPORT_TEMPLATE_PROCESSOR_ALIAS => 'oro_contact',
            ImportExportConfiguration::FIELD_IMPORT_PROCESSOR_ALIAS => $fieldImportProcessorAlias
        ]);
    }
}
