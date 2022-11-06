<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional;

use Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData;
use Symfony\Component\DomCrawler\Crawler;

class ContactPaginationTest extends AbstractContactPaginationTestCase
{
    public function testView()
    {
        $this->client->followRedirects(true);
        $crawler = $this->openEntity(
            'oro_contact_view',
            LoadContactEntitiesData::FIRST_ENTITY_NAME,
            $this->gridParams
        );
        $this->checkPaginationLinks($crawler);
    }

    public function testEdit()
    {
        $this->client->followRedirects(true);
        $crawler = $this->openEntity(
            'oro_contact_update',
            LoadContactEntitiesData::FIRST_ENTITY_NAME,
            $this->gridParams
        );
        $this->checkPaginationLinks($crawler);
    }

    public function testViewEditTrek()
    {
        $this->client->followRedirects(true);
        $crawler = $this->openEntity(
            'oro_contact_view',
            LoadContactEntitiesData::SECOND_ENTITY_NAME,
            $this->gridParams
        );
        $this->checkViewEditPagination($crawler, LoadContactEntitiesData::SECOND_ENTITY_NAME, 2, 4);

        // click edit button
        $edit    = $crawler->filter('.pull-right .edit-button')->link();
        $crawler = $this->client->click($edit);
        $this->checkViewEditPagination($crawler, LoadContactEntitiesData::SECOND_ENTITY_NAME, 2, 4);

        // save entity and stay on page
        $save = $crawler->selectButton('Save and Close')->form();
        $save->setValues(['input_action' => '{"route": "oro_contact_update", "params": {"id": "$id"}}']);
        $crawler = $this->client->submit($save);
        $this->checkViewEditPagination($crawler, LoadContactEntitiesData::SECOND_ENTITY_NAME, 2, 4);

        // save entity and go to view page
        $saveAndClose = $crawler->selectButton('Save and Close')->form();
        $redirectAction = $crawler->selectButton('Save and Close')->attr('data-action');
        $saveAndClose->setValues(['input_action' => $redirectAction]);
        $crawler      = $this->client->submit($saveAndClose);
        $this->checkViewEditPagination($crawler, LoadContactEntitiesData::SECOND_ENTITY_NAME, 2, 4);
    }

    /**
     * @dataProvider storageRebuildDataProvider
     */
    public function testStorageRebuild(bool $gridVisit, array $expected)
    {
        $this->client->followRedirects(true);
        $crawler = $this->openEntity(
            'oro_contact_view',
            LoadContactEntitiesData::FIRST_ENTITY_NAME,
            $this->gridParams
        );
        $this->checkPaginationLinks($crawler);

        if ($gridVisit) {
            $this->assertContactEntityGrid([]);
        }

        $crawler = $this->openEntity(
            'oro_contact_view',
            LoadContactEntitiesData::FOURTH_ENTITY_NAME,
            $this->gridParamsFiltered
        );
        $this->assertCurrentContactName($crawler, LoadContactEntitiesData::FOURTH_ENTITY_NAME);
        $this->assertPositionEntity($crawler, $expected['position'], $expected['total']);
    }

    public function storageRebuildDataProvider(): array
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

    private function checkViewEditPagination(Crawler $crawler, string $name, int $position, int $total): void
    {
        $this->assertCurrentContactName($crawler, $name);
        $this->assertPositionEntityLinks($crawler);
        $this->assertPositionEntity($crawler, $position, $total);
    }
}
