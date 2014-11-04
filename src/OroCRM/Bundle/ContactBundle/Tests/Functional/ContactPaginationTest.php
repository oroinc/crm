<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional;

use OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class ContactPaginationTest extends WebTestCase
{
    protected static $gridParams = ['contacts-grid' => 'i=1&p=25&s%5BlastName%5D=-1&s%5BfirstName%5D=-1'];

    protected function setUp()
    {
        $this->initClient(array(), $this->generateBasicAuthHeader());
        $this->loadFixtures(['OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData']);
    }

    public function testView()
    {
        $this->client->followRedirects(true);
        $this->assertEntityGrid();
        $crawler = $this->openEntity('orocrm_contact_view', LoadContactEntitiesData::FIRST_ENTITY_NAME);
        $this->checkPaginationLinks($crawler);
    }

    public function testEdit()
    {
        $this->client->followRedirects(true);
        $this->assertEntityGrid();
        $crawler = $this->openEntity('orocrm_contact_update', LoadContactEntitiesData::FIRST_ENTITY_NAME);
        $this->checkPaginationLinks($crawler);
    }

    protected function assertEntityGrid()
    {
        $this->client->request('GET', $this->getUrl('orocrm_contact_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    /**
     * @param string $route
     * @param string $name
     * @return Crawler
     */
    protected function openEntity($route, $name)
    {
        return $this->client->request(
            'GET',
            $this->getUrl(
                $route,
                [
                    'id' => $this->getContactByName($name)->getId(),
                    'grid' => self::$gridParams
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
    protected function assertPositionEntity(Crawler $crawler, $isFirst = false, $isLast = false)
    {
        if ($isFirst && $isLast) {
            $this->assertEquals(0, $crawler->filter('.user-info-state a:contains("First")')->count());
            $this->assertEquals(0, $crawler->filter('.user-info-state a:contains("Prev")')->count());
            $this->assertEquals(0, $crawler->filter('.user-info-state a:contains("Next")')->count());
            $this->assertEquals(0, $crawler->filter('.user-info-state a:contains("Last")')->count());
        } elseif ($isFirst) {
            $this->assertEquals(0, $crawler->filter('.user-info-state a:contains("First")')->count());
            $this->assertEquals(0, $crawler->filter('.user-info-state a:contains("Prev")')->count());
            $this->assertEquals(1, $crawler->filter('.user-info-state a:contains("Next")')->count());
            $this->assertEquals(1, $crawler->filter('.user-info-state a:contains("Last")')->count());
        } elseif ($isLast) {
            $this->assertEquals(1, $crawler->filter('.user-info-state a:contains("First")')->count());
            $this->assertEquals(1, $crawler->filter('.user-info-state a:contains("Prev")')->count());
            $this->assertEquals(0, $crawler->filter('.user-info-state a:contains("Next")')->count());
            $this->assertEquals(0, $crawler->filter('.user-info-state a:contains("Last")')->count());
        } else {
            $this->assertEquals(1, $crawler->filter('.user-info-state a:contains("First")')->count());
            $this->assertEquals(1, $crawler->filter('.user-info-state a:contains("Prev")')->count());
            $this->assertEquals(1, $crawler->filter('.user-info-state a:contains("Next")')->count());
            $this->assertEquals(1, $crawler->filter('.user-info-state a:contains("Last")')->count());
        }
    }

    /**
     * @param Crawler $crawler
     */
    protected function checkPaginationLinks(Crawler $crawler)
    {
        $this->assertCurrentContactName($crawler, LoadContactEntitiesData::FIRST_ENTITY_NAME);
        $this->assertPositionEntity($crawler, true);

        // click next link
        $next = $crawler->filter('.user-info-state a:contains("Next")')->link();
        $crawler = $this->client->click($next);

        $this->assertCurrentContactName($crawler, LoadContactEntitiesData::SECOND_ENTITY_NAME);
        $this->assertPositionEntity($crawler);

        // click last link
        $last = $crawler->filter('.user-info-state a:contains("Last")')->link();
        $crawler = $this->client->click($last);

        $this->assertCurrentContactName($crawler, LoadContactEntitiesData::FOURTH_ENTITY_NAME);
        $this->assertPositionEntity($crawler, false, true);

        // click previous link
        $previous = $crawler->filter('.user-info-state a:contains("Prev")')->link();
        $crawler = $this->client->click($previous);

        $this->assertCurrentContactName($crawler, LoadContactEntitiesData::THIRD_ENTITY_NAME);
        $this->assertPositionEntity($crawler);

        // click first link
        $first = $crawler->filter('.user-info-state a:contains("First")')->link();
        $crawler = $this->client->click($first);

        $this->assertCurrentContactName($crawler, LoadContactEntitiesData::FIRST_ENTITY_NAME);
        $this->assertPositionEntity($crawler, true);
    }
}
