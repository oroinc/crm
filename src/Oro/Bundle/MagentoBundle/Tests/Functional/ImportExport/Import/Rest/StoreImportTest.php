<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\ImportExport\Import\Rest;

use Oro\Bundle\MagentoBundle\Entity\Store;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\Rest\LoadMagentoRestChannel;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\Rest\LoadMagentoRestWebsite;

class StoreImportTest extends BaseIntegrationTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->markTestSkipped(
            'Channel type "magento2" is disabled. It should be enabled in CRM-8153'
        );

        $this->loadFixtures([
            LoadMagentoRestWebsite::class
        ]);
    }

    public function testInitImport()
    {
        $expectedStores = [
            [
                'id'   =>  0,
                'code' => 'admin',
                'name' =>  'Admin',
                'websiteId' => 0
            ],
            [
                'id'   =>  1,
                'code' => 'default',
                'name' =>  'Default Store',
                'websiteId' => 1
            ],
        ];
        $this->loadResponseFixture('store_init_import');
        $jobResult = $this->executeJob();
        $this->assertTrue($jobResult->isSuccessful());
        $this->assertStoresEquals($expectedStores);

        return $expectedStores;
    }

    /**
     * @depends testInitImport
     *
     * @param array $expectedStores
     */
    public function testImportWithException(array $expectedStores)
    {
        $this->loadResponseFixture('store_import_with_exception');
        $jobResult = $this->executeJob();
        $this->assertNotEmpty($jobResult->getFailureExceptions());
        $this->assertStoresEquals($expectedStores);
    }

    /**
     * @depends testInitImport
     *
     * @param array $expectedStores
     */
    public function testImportExistingData(array $expectedStores)
    {
        $this->loadResponseFixture('store_init_import');
        $jobResult = $this->executeJob();
        $this->assertTrue($jobResult->isSuccessful());
        $this->assertStoresEquals($expectedStores);
    }

    /**
     * @depends testInitImport
     */
    public function testImportNewData()
    {
        $this->loadResponseFixture('store_import_new_data');
        $jobResult = $this->executeJob();
        $this->assertTrue($jobResult->isSuccessful());
        $this->assertStoresEquals([
            [
                'id'   =>  0,
                'code' => 'admin',
                'name' =>  'Admin',
                'websiteId' => 0
            ],
            [
                'id'   =>  1,
                'code' => 'en',
                'name' =>  'USA Store',
                'websiteId' => 1
            ],
            [
                'id'   =>  2,
                'code' => 'es',
                'name' =>  'Spain Store',
                'websiteId' => 1
            ]
        ]);
    }

    protected function executeJob()
    {
        return $this->getContainer()->get('oro_importexport.job_executor')->executeJob(
            'import',
            'mage_store_rest_import',
            [
                'import' => [
                    'channel' => $this->getReference('default_integration_channel')->getId(),
                    'type' => LoadMagentoRestChannel::CHANNEL_TYPE
                ]
            ]
        );
    }

    /**
     * @param array $expectecStores
     */
    protected function assertStoresEquals(array $expectecStores)
    {
        $storeEntities = $this->em->getRepository(Store::class)->findBy([], ['originId' => 'ASC']);
        $stores = array_map(function (Store $item) {
            return [
                'id' =>   $item->getOriginId(),
                'code' => $item->getCode(),
                'name' => $item->getName(),
                'websiteId' => $item->getWebsite()->getOriginId()
            ];
        }, $storeEntities);
        $this->assertEquals($expectecStores, $stores);
    }
}
