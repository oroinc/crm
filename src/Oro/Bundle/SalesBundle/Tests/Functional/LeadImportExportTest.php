<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional;

use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfiguration;
use Oro\Bundle\ImportExportBundle\Tests\Functional\AbstractImportExportTestCase;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Repository\LeadRepository;
use Oro\Bundle\SalesBundle\ImportExport\Configuration\LeadImportExportConfigurationProvider;
use Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadLeadPhoneData;

/**
 * @dbIsolationPerTest
 */
class LeadImportExportTest extends AbstractImportExportTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([LoadLeadPhoneData::class]);
    }

    public function testExportTemplate(): void
    {
        $this->assertExportTemplateWorks(
            $this->getExportImportConfiguration(),
            $this->getFullPathToDataFile('lead_export_template.csv'),
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
                'Customer Customer Parent Parent Id',
                'Customer Customer Parent Owner Id',
                'Customer Customer Parent Name',
                'Customer Customer Owner Id'
            ]
        );
    }

    public function testExport(): void
    {
        $this->assertExportWorks(
            $this->getExportImportConfiguration(),
            $this->getFullPathToDataFile('lead_export.csv'),
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
                'Customer Customer Parent Parent Id',
                'Customer Customer Parent Owner Id',
                'Customer Customer Parent Name',
                'Customer Customer Owner Id',
                'Owner Username'
            ]
        );
    }

    public function testImportRecord(): void
    {
        $this->assertImportWorks(
            $this->getExportImportConfiguration(),
            $this->getFullPathToDataFile('lead_import_one_record.csv')
        );

        self::assertCount(4, $this->getLeadRepository()->findAll());
    }

    public function testImportDuplicatedAddressPhone(): void
    {
        $this->assertImportWorks(
            $this->getExportImportConfiguration(),
            $this->getFullPathToDataFile('lead_import_same_phone_address.csv')
        );

        $results = $this->getLeadRepository()->findAll();
        self::assertCount(5, $results);

        /** @var Lead $lead6 */
        $lead6 = $this->getLeadRepository()->findOneBy(['name' => 'Oro Inc. Lead Name6']);
        /** @var Lead $lead7 */
        $lead7 = $this->getLeadRepository()->findOneBy(['name' => 'Oro Inc. Lead Name7']);

        // Lead phone sand addresses don't have unique identifiers, they will always been created as new.
        self::assertFalse($lead6->getPhones()->isEmpty());
        self::assertFalse($lead6->getAddresses()->isEmpty());
        self::assertFalse($lead7->getPhones()->isEmpty());
        self::assertFalse($lead7->getAddresses()->isEmpty());
        self::assertNotSame($lead6->getPrimaryPhone(), $lead7->getPrimaryPhone());
        self::assertNotSame($lead6->getPrimaryAddress(), $lead7->getPrimaryAddress());
    }

    public function testImportLeadWithExistedContact(): void
    {
        $this->assertImportWorks(
            $this->getExportImportConfiguration(),
            $this->getFullPathToDataFile('lead_import_exist_contact.csv')
        );

        $results = $this->getLeadRepository()->findAll();
        self::assertCount(5, $results);

        /** @var Lead $lead7 */
        $lead7 = $this->getLeadRepository()->findOneBy(['name' => 'Oro Inc. Lead Name7']);
        /** @var Lead $lead8 */
        $lead8 = $this->getLeadRepository()->findOneBy(['name' => 'Oro Inc. Lead Name8']);

        self::assertSame($lead7->getContact(), $lead8->getContact());
    }

    private function getLeadRepository(): LeadRepository
    {
        return self::getContainer()->get('doctrine')->getRepository(Lead::class);
    }

    private function getFullPathToDataFile(string $fileName): string
    {
        $dataDir = $this->getContainer()
            ->get('kernel')
            ->locateResource('@OroSalesBundle/Tests/Functional/DataFixtures/Data');

        return $dataDir . DIRECTORY_SEPARATOR . $fileName;
    }

    private function getExportImportConfiguration(): ImportExportConfiguration
    {
        return (new LeadImportExportConfigurationProvider())->get();
    }
}
