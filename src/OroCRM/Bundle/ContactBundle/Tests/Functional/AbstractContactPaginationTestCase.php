<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional;

use Symfony\Component\DomCrawler\Crawler;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadUserData;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class AbstractContactPaginationTestCase extends WebTestCase
{
    /**
     * @var array
     */
    protected $gridParams         = [
        'contacts-grid' =>
            'i=1&p=25&s%5BlastName%5D=-1&s%5BfirstName%5D=-1'
    ];

    /**
     * @var array
     */
    protected $gridParamsFiltered = [
        'contacts-grid' =>
            'i=1&p=25&s%5BlastName%5D=-1&s%5BfirstName%5D=-1&f%5BfirstName%5D%5Bvalue%5D=F&f%5BfirstName%5D%5Btype%5D=1'
    ];

    protected function setUp()
    {
        LoadContactEntitiesData::$owner = LoadUserData::USER_NAME;
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures(['OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadUserData']);
        $this->loadFixtures(['OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData']);
        $this->client->mergeServerParameters($this->generateBasicAuthHeader(
            LoadUserData::USER_NAME,
            LoadUserData::USER_PASSWORD
        ));
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
     * @param string|null $expectedMessage
     * @return Crawler
     */
    protected function redirectViaFrontend($expectedMessage = null)
    {
        $response = $this->client->getResponse();
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('url', $data);

        if ($expectedMessage) {
            $this->assertArrayHasKey('message', $data);
            $this->assertEquals($expectedMessage, $data['message']);
        }

        return $this->client->request('GET', $data['url']);
    }

    /**
     * @param string $name
     *
     * @return Contact
     */
    protected function getContactByName($name)
    {
        $contact = $this->getContainer()->get('doctrine')
            ->getRepository('OroCRMContactBundle:Contact')
            ->findOneBy(['firstName' => $name]);

        // guard
        $this->assertNotNull($contact);

        return $contact;
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

        $this->assertEquals(
            (int)$showFirst,
            $crawler->filter('#entity-pagination li:not(.disabled) a:contains("First")')->count()
        );
        $this->assertEquals(
            (int)$showPrev,
            $crawler->filter('#entity-pagination li:not(.disabled) a .icon-chevron-left')->count()
        );
        $this->assertEquals(
            (int)$showNext,
            $crawler->filter('#entity-pagination li:not(.disabled) a .icon-chevron-right')->count()
        );
        $this->assertEquals(
            (int)$showLast,
            $crawler->filter('#entity-pagination li:not(.disabled) a:contains("Last")')->count()
        );
    }

    /**
     * @param Crawler $crawler
     * @param int $position
     * @param int $total
     */
    protected function assertPositionEntity(Crawler $crawler, $position, $total)
    {
        $this->assertEquals((string)$position, $crawler->filter('#entity-pagination .pagination-current')->html());
        $this->assertContains((string)$total, $crawler->filter('#entity-pagination .pagination-total')->html());
    }

    /**
     * @param Crawler $crawler
     */
    protected function checkPaginationLinks(Crawler $crawler)
    {
        $this->assertCurrentContactName($crawler, LoadContactEntitiesData::FIRST_ENTITY_NAME);
        $this->assertPositionEntityLinks($crawler, true);
        $this->assertPositionEntity($crawler, 1, 4);

        // click next link
        $next = $crawler->filter('#entity-pagination a .icon-chevron-right')->parents()->link();
        $this->client->click($next);
        $crawler = $this->redirectViaFrontend();

        $this->assertCurrentContactName($crawler, LoadContactEntitiesData::SECOND_ENTITY_NAME);
        $this->assertPositionEntityLinks($crawler);
        $this->assertPositionEntity($crawler, 2, 4);

        // click last link
        $last = $crawler->filter('#entity-pagination a:contains("Last")')->link();
        $this->client->click($last);
        $crawler = $this->redirectViaFrontend();

        $this->assertCurrentContactName($crawler, LoadContactEntitiesData::FOURTH_ENTITY_NAME);
        $this->assertPositionEntityLinks($crawler, false, true);
        $this->assertPositionEntity($crawler, 4, 4);

        // click previous link
        $previous = $crawler->filter('#entity-pagination a .icon-chevron-left')->parents()->link();
        $this->client->click($previous);
        $crawler = $this->redirectViaFrontend();

        $this->assertCurrentContactName($crawler, LoadContactEntitiesData::THIRD_ENTITY_NAME);
        $this->assertPositionEntityLinks($crawler);
        $this->assertPositionEntity($crawler, 3, 4);

        // click first link
        $first = $crawler->filter('#entity-pagination a:contains("First")')->link();
        $this->client->click($first);
        $crawler = $this->redirectViaFrontend();

        $this->assertCurrentContactName($crawler, LoadContactEntitiesData::FIRST_ENTITY_NAME);
        $this->assertPositionEntityLinks($crawler, true);
        $this->assertPositionEntity($crawler, 1, 4);
    }
}
