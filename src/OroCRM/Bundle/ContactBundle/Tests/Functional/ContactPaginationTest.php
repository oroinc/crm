<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional;

use OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData;

use Symfony\Component\DomCrawler\Crawler;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class ContactPaginationTest extends AbstractContactPaginationTestCase
{
    public function testView()
    {
        $this->client->followRedirects(true);
        $crawler = $this->openEntity(
            'orocrm_contact_view',
            LoadContactEntitiesData::FIRST_ENTITY_NAME,
            $this->gridParams
        );
        $this->checkPaginationLinks($crawler);
    }

    public function testEdit()
    {
        $this->client->followRedirects(true);
        $crawler = $this->openEntity(
            'orocrm_contact_update',
            LoadContactEntitiesData::FIRST_ENTITY_NAME,
            $this->gridParams
        );
        $this->checkPaginationLinks($crawler);
    }

    public function testViewEditTrek()
    {
        $this->client->followRedirects(true);
        $crawler = $this->openEntity(
            'orocrm_contact_view',
            LoadContactEntitiesData::SECOND_ENTITY_NAME,
            $this->gridParams
        );
        $this->checkViewEditPagination($crawler, LoadContactEntitiesData::SECOND_ENTITY_NAME, 2, 4);

        // click edit button
        $edit    = $crawler->filter('.pull-right .edit-button')->link();
        $crawler = $this->client->click($edit);
        $this->checkViewEditPagination($crawler, LoadContactEntitiesData::SECOND_ENTITY_NAME, 2, 4);

        // save entity and stay on page
        $save    = $crawler->selectButton('Save and Close')->form();
        $save->setValues(['input_action' => 'save_and_stay']);
        $crawler = $this->client->submit($save);
        $this->checkViewEditPagination($crawler, LoadContactEntitiesData::SECOND_ENTITY_NAME, 2, 4);

        // save entity and go to view page
        $saveAndClose = $crawler->selectButton('Save and Close')->form();
        $crawler      = $this->client->submit($saveAndClose);
        $this->checkViewEditPagination($crawler, LoadContactEntitiesData::SECOND_ENTITY_NAME, 2, 4);
    }

    /**
     * @param bool $gridVisit
     * @param string $expected
     *
     * @dataProvider storageRebuildDataProvider
     */
    public function testStorageRebuild($gridVisit, $expected)
    {
        $this->client->followRedirects(true);
        $crawler = $this->openEntity(
            'orocrm_contact_view',
            LoadContactEntitiesData::FIRST_ENTITY_NAME,
            $this->gridParams
        );
        $this->checkPaginationLinks($crawler);

        if ($gridVisit) {
            $this->assertContactEntityGrid([]);
        }

        $crawler = $this->openEntity(
            'orocrm_contact_view',
            LoadContactEntitiesData::FOURTH_ENTITY_NAME,
            $this->gridParamsFiltered
        );
        $this->assertCurrentContactName($crawler, LoadContactEntitiesData::FOURTH_ENTITY_NAME);
        $this->assertPositionEntity($crawler, $expected['position'], $expected['total']);
    }

    /**
     * @return array
     */
    public function storageRebuildDataProvider()
    {
        return [
            'visit grid' => [
                'gridVisit' => true,
                'expected'  => [
                    'position' => 1,
                    'total'    => 1
                ]
            ],
            'shared link' => [
                'gridVisit' => false,
                'expected'  => [
                    'position' => 1,
                    'total'    => 1
                ]
            ],
        ];
    }

    /**
     * @param Crawler $crawler
     * @param $name
     * @param int $position
     * @param int $total
     */
    protected function checkViewEditPagination(Crawler $crawler, $name, $position, $total)
    {
        $this->assertCurrentContactName($crawler, $name);
        $this->assertPositionEntityLinks($crawler);
        $this->assertPositionEntity($crawler, $position, $total);
    }
}
