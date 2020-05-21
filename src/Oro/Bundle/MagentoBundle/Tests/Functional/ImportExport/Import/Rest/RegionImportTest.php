<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\ImportExport\Import\Rest;

use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\MagentoBundle\Entity\Region;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\Rest\LoadMagentoRestChannel;

/**
 * Class RegionImportTest
 * @package Oro\Bundle\MagentoBundle\Tests\Functional\ImportExport\Import\Rest
 */
class RegionImportTest extends BaseIntegrationTest
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
        $doctrine = $this->getContainer()->get('doctrine');
        $manager = $doctrine->getManagerForClass(Region::class);
        $manager->createQuery('DELETE FROM OroMagentoBundle:Region')->execute();
        $expectedRegions = [
            [
                "combinedCode" => "FR-1",
                "code" => "1",
                "countryCode" => "FR",
                "regionId" => 182,
                "name" => "Ain"
            ],
            [
                "combinedCode" => "FR-2",
                "code" => "2",
                "countryCode" => "FR",
                "regionId" => 183,
                "name" => "Aisne"
            ]
        ];
        $this->loadResponseFixture('region_init_import');
        $jobResult = $this->executeJob();
        $this->assertTrue($jobResult->isSuccessful());
        $this->assertRegionsEquals($expectedRegions);

        return $expectedRegions;
    }

    /**
     * @depends testInitImport
     *
     * @param array $expectedRegions
     */
    public function testImportWithException(array $expectedRegions)
    {
        $this->loadResponseFixture('region_import_with_exception');
        $jobResult = $this->executeJob();
        $this->assertNotEmpty($jobResult->getFailureExceptions());
        $this->assertRegionsEquals($expectedRegions);
    }

    /**
     * @depends testInitImport
     *
     * @param array $expectedRegions
     */
    public function testImportExistingData(array $expectedRegions)
    {
        $this->loadResponseFixture('region_init_import');
        $jobResult = $this->executeJob();
        $this->assertTrue($jobResult->isSuccessful());
        $this->assertRegionsEquals($expectedRegions);
    }

    /**
     * @depends testInitImport
     *
     * @param array $expectedRegions
     */
    public function testImportNewData(array $expectedRegions)
    {
        $this->loadResponseFixture('region_import_new_data');
        $jobResult = $this->executeJob();
        $this->assertTrue($jobResult->isSuccessful());
        $addedRegions = [
            [
                "combinedCode" => "RM-1",
                "code" => "1",
                "countryCode" => "RM",
                "regionId" => 192,
                "name" => "Abn"
            ],
            [
                "combinedCode" => "RM-2",
                "code" => "2",
                "countryCode" => "RM",
                "regionId" => 193,
                "name" => "Arn"
            ]
        ];
        $expectedRegions = array_merge($expectedRegions, $addedRegions);
        $this->assertRegionsEquals($expectedRegions);
    }

    protected function executeJob()
    {
        return $this->getContainer()->get('oro_importexport.job_executor')->executeJob(
            'import',
            'mage_region_import',
            [
                ProcessorRegistry::TYPE_IMPORT => [
                    'processorAlias' => 'oro_magento.add_or_update_region',
                    'channel' => $this->getReference('default_integration_channel')->getId(),
                    'type' => LoadMagentoRestChannel::CHANNEL_TYPE
                ]
            ]
        );
    }

    /**
     * @param array $expectedRegions
     */
    protected function assertRegionsEquals(array $expectedRegions)
    {
        $regionEntities = $this->em->getRepository(Region::class)->findBy([], ['id' => 'ASC']);
        $regions = array_map(function (Region $item) {
            return [
                "combinedCode" => $item->getCombinedCode(),
                "code" => $item->getCode(),
                "countryCode" => $item->getCountryCode(),
                "regionId" => $item->getRegionId(),
                "name" => $item->getName()
            ];
        }, $regionEntities);

        $this->assertEquals(
            $expectedRegions,
            $regions,
            'Regions list must be equals to expected !'
        );
    }
}
