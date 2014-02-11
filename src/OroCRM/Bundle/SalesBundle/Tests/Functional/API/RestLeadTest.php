<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class RestLeadTest extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    public function setUp()
    {
        $this->client = static::createClient(
            array(),
            ToolsAPI::generateWsseHeader()
        );
    }

    /**
     * @return array
     */
    public function testPostLead()
    {
        $request = array(
           "lead" => array(
                'name' => 'lead_name_' . mt_rand(1, 500),
                'firstName' => 'first_name_' . mt_rand(1, 500),
                'lastName' => 'last_name_' . mt_rand(1, 500),
                'owner' => '1'
           )
        );

        $this->client->request(
            'POST',
            $this->client->generate('oro_api_post_lead'),
            $request
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 201);
        $result = ToolsAPI::jsonToArray($result->getContent());

        $request['id'] = $result['id'];
        return $request;
    }

    /**
     * @param $request
     * @depends testPostLead
     * @return mixed
     */
    public function testGetLead($request)
    {
        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_lead', array('id' => $request['id']))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());

        $this->assertEquals($request['id'], $result['id']);
        $this->assertEquals($request['lead']['firstName'], $result['firstName']);
        $this->assertEquals($request['lead']['lastName'], $result['lastName']);
        $this->assertEquals($request['lead']['name'], $result['name']);
        $this->assertEquals('New', $result['status']);
        // TODO: incomplete CRM-816
        //$this->assertEquals($request['lead']['owner'], $result['owner']['id']);
        return $request;
    }

    /**
     * @param $request
     * @depends testGetLead
     * @return mixed
     */
    public function testPutLead($request)
    {

        $request['lead']['firstName'] .= '_updated';
        $request['lead']['lastName'] .= '_updated';
        $request['lead']['name'] .= '_updated';

        $this->client->request(
            'PUT',
            $this->client->generate('oro_api_put_lead', array('id' => $request['id'])),
            $request
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);

        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_lead', array('id' => $request['id']))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());

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
    public function testGetLeads($request)
    {
        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_leads')
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsApi::jsonToArray($result->getContent());

        $this->assertNotEmpty($result);

        $result = reset($result);
        $this->assertEquals($request['id'], $result['id']);
        $this->assertEquals($request['lead']['firstName'], $result['firstName']);
        $this->assertEquals($request['lead']['lastName'], $result['lastName']);
        $this->assertEquals($request['lead']['name'], $result['name']);
        $this->assertEquals('New', $result['status']);
    }

    /**
     * @depends testPutLead
     */
    public function testDeleteLead($request)
    {
        $this->client->request(
            'DELETE',
            $this->client->generate('oro_api_delete_lead', array('id' => $request['id']))
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);

        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_lead', array('id' => $request['id']))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 404);
    }
}
