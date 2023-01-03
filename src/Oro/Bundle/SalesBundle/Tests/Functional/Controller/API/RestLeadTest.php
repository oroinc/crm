<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Controller\API;

use Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class RestLeadTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateWsseAuthHeader()
        );
        $this->loadFixtures([LoadSalesBundleFixtures::class]);
    }

    public function testPostLead(): array
    {
        $request = [
            'lead' => [
                'name'      => 'lead_name_' . random_int(1, 500),
                'firstName' => 'first_name_' . random_int(1, 500),
                'lastName'  => 'last_name_' . random_int(1, 500),
                'owner'     => '1',
            ]
        ];

        $this->client->request(
            'POST',
            $this->getUrl('oro_api_post_lead'),
            $request
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 201);

        $request['id'] = $result['id'];

        return $request;
    }

    /**
     * @depends testPostLead
     */
    public function testGetLead(array $request): array
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_lead', ['id' => $request['id']])
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals($request['id'], $result['id']);
        $this->assertEquals($request['lead']['firstName'], $result['firstName']);
        $this->assertEquals($request['lead']['lastName'], $result['lastName']);
        $this->assertEquals($request['lead']['name'], $result['name']);
        $this->assertEquals('New', $result['status']);
        // Incomplete CRM-816
        //$this->assertEquals($request['lead']['owner'], $result['owner']['id']);

        return $request;
    }

    /**
     * @depends testGetLead
     */
    public function testPutLead(array $request): array
    {
        $request['lead']['firstName'] .= '_updated';
        $request['lead']['lastName'] .= '_updated';
        $request['lead']['name'] .= '_updated';

        $this->client->request(
            'PUT',
            $this->getUrl('oro_api_put_lead', ['id' => $request['id']]),
            $request
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_lead', ['id' => $request['id']])
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals($request['id'], $result['id']);
        $this->assertEquals($request['lead']['firstName'], $result['firstName']);
        $this->assertEquals($request['lead']['lastName'], $result['lastName']);
        $this->assertEquals($request['lead']['name'], $result['name']);
        $this->assertEquals('New', $result['status']);

        return $request;
    }

    /**
     * @depends testPutLead
     */
    public function testGetLeads(array $request)
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_leads')
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertNotEmpty($result);

        $result = end($result);
        $this->assertEquals($request['id'], $result['id']);
        $this->assertEquals($request['lead']['firstName'], $result['firstName']);
        $this->assertEquals($request['lead']['lastName'], $result['lastName']);
        $this->assertEquals($request['lead']['name'], $result['name']);
        $this->assertEquals('New', $result['status']);
    }

    /**
     * @depends testPutLead
     */
    public function testDeleteLead(array $request)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl('oro_api_delete_lead', ['id' => $request['id']])
        );
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_lead', ['id' => $request['id']])
        );

        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 404);
    }
}
