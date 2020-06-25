<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CustomerSearchControllerTest extends WebTestCase
{
    /** @var string */
    protected $baseUrl;

    protected function setUp(): void
    {
        $this->markTestSkipped('Magento integration is disabled in CRM-9202');
        $this->markTestSkipped('Due to BAP-8365');

        $this->initClient([], $this->generateWsseAuthHeader());
        $this->loadFixtures(['Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadCustomerData']);

        $this->baseUrl = $this->getUrl('oro_api_get_search_customers');
    }

    public function testSearch()
    {
        $this->client->request('GET', $this->baseUrl);
        $entities = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertNotEmpty($entities);
        $this->assertCount(4, $entities);
    }

    public function testSearchWithFilter()
    {
        $this->client->request('GET', $this->baseUrl . '?search=John');
        $entities = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertNotEmpty($entities);
        $this->assertCount(1, $entities);

        // Check searching by non-existing customer name. Should return no results.
        $this->client->request('GET', $this->baseUrl . sprintf('?search=%s', 'NonExistentCustomerName'));
        $entities = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertEmpty($entities);
    }

    public function testEmailSearchWithPaging()
    {
        $this->client->request(
            'GET',
            $this->baseUrl . '?page=2&limit=3',
            [],
            [],
            ['HTTP_X-Include' => 'totalCount']
        );
        $response = $this->client->getResponse();
        $entities = $this->getJsonResponseContent($response, 200);
        $this->assertCount(1, $entities);
        $this->assertEquals(4, $response->headers->get('X-Include-Total-Count'));
    }
}
