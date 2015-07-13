<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Functional\Controller\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class RestCustomerSearchTest extends WebTestCase
{
    /** @var string */
    protected $baseUrl;

    protected function setUp()
    {
        $this->markTestSkipped('Due to BAP-8365');

        $this->initClient([], $this->generateWsseAuthHeader());
        $this->loadFixtures(['OroCRM\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures']);

        $this->baseUrl = $this->getUrl('oro_api_get_search_customers');
    }

    public function testSearch()
    {
        $this->client->request('GET', $this->baseUrl);
        $entities = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertNotEmpty($entities);
        $this->assertCount(1, $entities);
    }

    public function testSearchWithFilter()
    {
        $this->client->request('GET', $this->baseUrl . '?search=b2bCustomer name');
        $entities = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertNotEmpty($entities);
        $this->assertCount(1, $entities);

        // Check searching by non-existing customer name. Should return no results.
        $this->client->request('GET', $this->baseUrl . sprintf('?search=%s', 'NonExistentCustomerName'));
        $entities = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertEmpty($entities);
    }
}
