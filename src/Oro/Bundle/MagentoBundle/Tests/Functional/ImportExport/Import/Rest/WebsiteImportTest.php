<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\ImportExport\Import\Rest;

use Oro\Bundle\MagentoBundle\Entity\Website;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\Rest\LoadMagentoRestChannel;

class WebsiteImportTest extends BaseIntegrationTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->markTestSkipped(
            'Channel type "magento2" is disabled. It should be enabled in CRM-8153'
        );

        $this->loadFixtures([
            LoadMagentoRestChannel::class
        ]);
    }

    public function testInitImport()
    {
        $expectedWebsites = [
            [
                "id" => 0,
                "code" => "admin",
                "name" => "Admin",
                "defaultGroupId" => 0
            ],
            [
                "id" => 1,
                "code" => "base",
                "name" => "Main Website",
                "defaultGroupId" => 1
            ]
        ];
        $this->loadResponseFixture('website_init_import');
        $jobResult = $this->executeJob();
        $this->assertTrue($jobResult->isSuccessful());
        $this->assertWebsitesEquals($expectedWebsites);

        return $expectedWebsites;
    }

    /**
     * @depends testInitImport
     *
     * @param array $expectedWebsites
     */
    public function testImportWithException(array $expectedWebsites)
    {
        $this->loadResponseFixture('website_import_with_exception');
        $jobResult = $this->executeJob();
        $this->assertNotEmpty($jobResult->getFailureExceptions());
        $this->assertWebsitesEquals($expectedWebsites);
    }

    /**
     * @depends testInitImport
     *
     * @param array $expectedWebsites
     */
    public function testImportExistingData(array $expectedWebsites)
    {
        $this->loadResponseFixture('website_init_import');
        $jobResult = $this->executeJob();
        $this->assertTrue($jobResult->isSuccessful());
        $this->assertWebsitesEquals($expectedWebsites);
    }

    /**
     * @depends testInitImport
     */
    public function testImportNewData()
    {
        $this->loadResponseFixture('website_import_new_data');
        $jobResult = $this->executeJob();
        $this->assertTrue($jobResult->isSuccessful());
        $this->assertWebsitesEquals(
            [
                [
                    "id" => 0,
                    "code" => "admin",
                    "name" => "Admin",
                    "defaultGroupId" => 0
                ],
                [
                    "id" => 1,
                    "code" => "base",
                    "name" => "New name of main website",
                    "defaultGroupId" => 2
                ],
                [
                    "id" => 2,
                    "code" => "spare_website",
                    "name" => "Spare Website",
                    "defaultGroupId" => 1
                ]
            ]
        );
    }

    protected function executeJob()
    {
        return $this->getContainer()->get('oro_importexport.job_executor')->executeJob(
            'import',
            'mage_website_rest_import',
            [
                'import' => [
                    'channel' => $this->getReference('default_integration_channel')->getId(),
                    'type' => LoadMagentoRestChannel::CHANNEL_TYPE
                ]
            ]
        );
    }

    /**
     * @param array $expectedWebsites
     */
    protected function assertWebsitesEquals(array $expectedWebsites)
    {
        $websiteEntities = $this->em->getRepository(Website::class)->findBy([], ['originId' => 'ASC']);
        $websites = array_map(function (Website $item) {
            return [
                'id' =>   $item->getOriginId(),
                'code' => $item->getCode(),
                'name' => $item->getName(),
                'defaultGroupId' => $item->getDefaultGroupId()
            ];
        }, $websiteEntities);
        $this->assertEquals(
            $expectedWebsites,
            $websites,
            'Website list must be equals to expected !'
        );
    }
}
