<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfiguration;
use Oro\Bundle\ImportExportBundle\Tests\Functional\AbstractImportExportTest;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Entity\Repository\OpportunityRepository;
use Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadOpenOpportunityFixtures;

/**
 * @dbIsolationPerTest
 */
class ImportExportTest extends AbstractImportExportTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures(
            [
                LoadOpenOpportunityFixtures::class,
            ]
        );
    }

    public function testExportTemplate()
    {
        $this->assertExportTemplateWorks(
            $this->getExportImportConfiguration(),
            $this->getFullPathToDataFile('export_template.csv'),
            [
                'Id',
                'Customer Business Customer Id',
                'Organization Name',
                'Customer Business Customer Organization Name',
                'Customer Customer Id',
                'Customer Customer Name',
                'Customer Customer Parent Id',
                'Customer Customer Group Name',
                'Customer Customer Owner Username',
                'Customer Customer Tax code',
                'Customer Customer Account Id',
                'Customer Customer VAT Id',
                'Customer Customer Internal rating Id',
                'Customer Customer Payment term Label',
                'Channel Name',
                'Customer Customer Parent Parent Id',
                'Customer Customer Parent Owner Id',
                'Customer Customer Owner Id',
            ]
        );
    }

    public function testExport()
    {
        $this->assertExportWorks(
            $this->getExportImportConfiguration(),
            $this->getFullPathToDataFile('export.csv'),
            [
                'Id',
                'Customer Business Customer Id',
                'Organization Name',
                'Customer Business Customer Organization Name',
                'Customer Customer Id',
                'Customer Customer Name',
                'Customer Customer Parent Id',
                'Customer Customer Group Name',
                'Customer Customer Owner Username',
                'Customer Customer Tax code',
                'Customer Customer Account Id',
                'Customer Customer VAT Id',
                'Customer Customer Internal rating Id',
                'Customer Customer Payment term Label',
                'Channel Name',
                'Customer Customer Parent Parent Id',
                'Customer Customer Parent Owner Id',
                'Customer Customer Owner Id',
            ]
        );
    }

    public function testImportRecordWithAddOrReplaceStrategy()
    {
        $this->assertImportWorks(
            $this->getExportImportConfiguration(),
            $this->getFullPathToDataFile('import_one_record.csv')
        );

        static::assertCount(
            1,
            $this->getRepository()->findAll()
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
     * @return OpportunityRepository
     */
    private function getRepository()
    {
        return static::getContainer()
            ->get('doctrine')
            ->getManagerForClass(Contact::class)
            ->getRepository(Contact::class);
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
            ->locateResource('@OroSalesBundle/Tests/Functional/DataFixtures/Data');

        return $dataDir . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * @return ImportExportConfiguration
     */
    private function getExportImportConfiguration(): ImportExportConfiguration
    {
        return new ImportExportConfiguration([
            ImportExportConfiguration::FIELD_ENTITY_CLASS => Opportunity::class,
            ImportExportConfiguration::FIELD_EXPORT_PROCESSOR_ALIAS => 'oro_sales_opportunity',
            ImportExportConfiguration::FIELD_EXPORT_TEMPLATE_PROCESSOR_ALIAS => 'oro_sales_opportunity',
            ImportExportConfiguration::FIELD_IMPORT_PROCESSOR_ALIAS => 'oro_sales_opportunity.add_or_replace'
        ]);
    }
}
