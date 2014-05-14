<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\Client;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class RestOpportunityTest extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    public function setUp()
    {
        $this->client = self::createClient(
            array(),
            $this->generateWsseAuthHeader()
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

        $result = $this->getJsonResponseContent($this->client->getResponse(), 201);

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

        $result = $this->getJsonResponseContent($this->client->getResponse(), 201);

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

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

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
        $this->assertJsonResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_opportunity', array('id' => $request['id']))
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

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

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

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
        $this->assertJsonResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_opportunity', array('id' => $request['id']))
        );

        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 404);
    }
}
