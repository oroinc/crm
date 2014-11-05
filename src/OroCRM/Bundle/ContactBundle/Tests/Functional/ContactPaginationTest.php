<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

use Symfony\Component\DomCrawler\Crawler;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class ContactPaginationTest extends WebTestCase
{
    protected $gridParams         = ['contacts-grid' => 'i=1&p=25&s%5BlastName%5D=-1&s%5BfirstName%5D=-1'];
    protected $gridParamsFiltered = [
        'contacts-grid' =>
            'i=1&p=25&s%5BlastName%5D=-1&s%5BfirstName%5D=-1&f%5BfirstName%5D%5Bvalue%5D=f&f%5BfirstName%5D%5Btype%5D=1'
    ];

    protected function setUp()
    {
        $this->initClient(array(), $this->generateBasicAuthHeader());
        $this->loadFixtures(['OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData']);
    }

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
        $this->checkViewEditPagination($crawler, LoadContactEntitiesData::SECOND_ENTITY_NAME, '2 of 4');

        // click edit button
        $edit    = $crawler->filter('.pull-right .edit-button')->link();
        $crawler = $this->client->click($edit);
        $this->checkViewEditPagination($crawler, LoadContactEntitiesData::SECOND_ENTITY_NAME, '2 of 4');

        // save entity and stay on page
        $save    = $crawler->selectButton('Save and Close')->form();
        $save->setValues(['input_action' => 'save_and_stay']);
        $crawler = $this->client->submit($save);
        $this->checkViewEditPagination($crawler, LoadContactEntitiesData::SECOND_ENTITY_NAME, '2 of 4');

        // save entity and go to view page
        $saveAndClose = $crawler->selectButton('Save and Close')->form();
        $crawler      = $this->client->submit($saveAndClose);
        $this->checkViewEditPagination($crawler, LoadContactEntitiesData::SECOND_ENTITY_NAME, '2 of 4');
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
        $this->assertPositionEntity($crawler, $expected);
    }

    /**
     * @return array
     */
    public function storageRebuildDataProvider()
    {
        return [
            'visit grid' => [
                'gridVisit' => true,
                'expected'  => '1 of 1'
            ],
            'shared link' => [
                'gridVisit' => false,
                'expected'  => '1 of 1'
            ],
        ];
    }

    /**
     * @param Crawler $crawler
     * @param $name
     * @param string $position
     */
    protected function checkViewEditPagination(Crawler $crawler, $name, $position)
    {
        $this->assertCurrentContactName($crawler, $name);
        $this->assertPositionEntityLinks($crawler);
        $this->assertPositionEntity($crawler, $position);
    }

    /**
     * @param array $params
     */
    protected function assertContactEntityGrid($params = [])
    {
        $this->client->request('GET', $this->getUrl('orocrm_contact_index', $params));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    /**
     * @param string $route
     * @param string $name
     * @param array $gridParams
     * @return Crawler
     */
    protected function openEntity($route, $name, array $gridParams)
    {
        return $this->client->request(
            'GET',
            $this->getUrl(
                $route,
                [
                    'id' => $this->getContactByName($name)->getId(),
                    'grid' => $gridParams
                ]
            )
        );
    }

    /**
     * @param string $name
     * @return Contact
     */
    protected function getContactByName($name)
    {
        return $this->getContainer()->get('doctrine')
            ->getRepository('OroCRMContactBundle:Contact')
            ->findOneBy(['firstName' => $name]);
    }

    /**
     * @param Crawler $crawler
     * @param string $name
     */
    protected function assertCurrentContactName(Crawler $crawler, $name)
    {
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains($name, $crawler->filter('h1.user-name')->html());
    }

    /**
     * @param Crawler $crawler
     * @param bool $isFirst
     * @param bool $isLast
     */
    protected function assertPositionEntityLinks(Crawler $crawler, $isFirst = false, $isLast = false)
    {
        $showFirst = !$isFirst;
        $showPrev  = !$isFirst;
        $showLast  = !$isLast;
        $showNext  = !$isLast;

        $this->assertEquals((int)$showFirst, $crawler->filter('.entity-pagination a:contains("First")')->count());
        $this->assertEquals((int)$showPrev, $crawler->filter('.entity-pagination a:contains("Prev")')->count());
        $this->assertEquals((int)$showNext, $crawler->filter('.entity-pagination a:contains("Next")')->count());
        $this->assertEquals((int)$showLast, $crawler->filter('.entity-pagination a:contains("Last")')->count());
    }

    /**
     * @param Crawler $crawler
     * @param string $position
     */
    protected function assertPositionEntity(Crawler $crawler, $position)
    {
        $this->assertEquals($position, $crawler->filter('.entity-pagination span')->html());
    }

    /**
     * @param Crawler $crawler
     */
    protected function checkPaginationLinks(Crawler $crawler)
    {
        $this->assertCurrentContactName($crawler, LoadContactEntitiesData::FIRST_ENTITY_NAME);
        $this->assertPositionEntityLinks($crawler, true);
        $this->assertPositionEntity($crawler, '1 of 4');

        // click next link
        $next = $crawler->filter('.entity-pagination a:contains("Next")')->link();
        $crawler = $this->client->click($next);

        $this->assertCurrentContactName($crawler, LoadContactEntitiesData::SECOND_ENTITY_NAME);
        $this->assertPositionEntityLinks($crawler);
        $this->assertPositionEntity($crawler, '2 of 4');

        // click last link
        $last = $crawler->filter('.entity-pagination a:contains("Last")')->link();
        $crawler = $this->client->click($last);

        $this->assertCurrentContactName($crawler, LoadContactEntitiesData::FOURTH_ENTITY_NAME);
        $this->assertPositionEntityLinks($crawler, false, true);
        $this->assertPositionEntity($crawler, '4 of 4');

        // click previous link
        $previous = $crawler->filter('.entity-pagination a:contains("Prev")')->link();
        $crawler = $this->client->click($previous);

        $this->assertCurrentContactName($crawler, LoadContactEntitiesData::THIRD_ENTITY_NAME);
        $this->assertPositionEntityLinks($crawler);
        $this->assertPositionEntity($crawler, '3 of 4');

        // click first link
        $first = $crawler->filter('.entity-pagination a:contains("First")')->link();
        $crawler = $this->client->click($first);

        $this->assertCurrentContactName($crawler, LoadContactEntitiesData::FIRST_ENTITY_NAME);
        $this->assertPositionEntityLinks($crawler, true);
        $this->assertPositionEntity($crawler, '1 of 4');
    }
}
