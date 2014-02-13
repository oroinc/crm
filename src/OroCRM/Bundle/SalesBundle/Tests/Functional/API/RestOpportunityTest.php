<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class RestOpportunityTest extends WebTestCase
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

    protected function preAccountData()
    {
        $request = array(
            "account" => array (
                "name" => 'Account_name_opportunity',
                "owner" => '1',
            )
        );
        $this->client->request(
            'POST',
            $this->client->generate('oro_api_post_account'),
            $request
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 201);
        $result = ToolsAPI::jsonToArray($result->getContent());
        return $result['id'];
    }
    /**
     * @return array
     */
    public function testPostOpportunity()
    {
        $request = array(
           "opportunity" => array(
               'name' => 'opportunity_name_' . mt_rand(1, 500),
               'owner' => '1',
               'account' => $this->preAccountData()
           )
        );

        $this->client->request(
            'POST',
            $this->client->generate('oro_api_post_opportunity'),
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
     * @depends testPostOpportunity
     * @return mixed
     */
    public function testGetOpportunity($request)
    {
        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_opportunity', array('id' => $request['id']))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());

        $this->assertEquals($request['id'], $result['id']);
        $this->assertEquals($request['opportunity']['name'], $result['name']);
        $this->assertEquals('In Progress', $result['status']);
        $this->assertEquals('Account_name_opportunity', $result['account']);
        // TODO: incomplete CRM-816
        //$this->assertEquals($request['opportunity']['owner'], $result['owner']['id']);
        return $request;
    }

    /**
     * @param $request
     * @depends testGetOpportunity
     * @return mixed
     */
    public function testPutOpportunity($request)
    {

        $request['opportunity']['name'] .= '_updated';

        $this->client->request(
            'PUT',
            $this->client->generate('oro_api_put_opportunity', array('id' => $request['id'])),
            $request
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);

        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_opportunity', array('id' => $request['id']))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());

        $this->assertEquals($request['id'], $result['id']);
        $this->assertEquals($request['opportunity']['name'], $result['name']);
        $this->assertEquals('In Progress', $result['status']);

        return $request;
    }

    /**
     * @depends testPutOpportunity
     */
    public function testGetOpportunitys($request)
    {
        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_opportunities')
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsApi::jsonToArray($result->getContent());

        $this->assertNotEmpty($result);

        $result = reset($result);
        $this->assertEquals($request['id'], $result['id']);
        $this->assertEquals($request['opportunity']['name'], $result['name']);
        $this->assertEquals('In Progress', $result['status']);
    }

    /**
     * @depends testPutOpportunity
     */
    public function testDeleteOpportunity($request)
    {
        $this->client->request(
            'DELETE',
            $this->client->generate('oro_api_delete_opportunity', array('id' => $request['id']))
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);

        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_opportunity', array('id' => $request['id']))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 404);
    }
}
