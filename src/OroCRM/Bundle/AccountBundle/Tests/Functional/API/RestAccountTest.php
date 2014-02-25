<?php

namespace OroCRM\Bundle\AccountBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class RestAccountTest extends WebTestCase
{
    /** @var Client */
    protected $client;

    public function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());
    }

    public function testCreate()
    {
        $request = array(
            "account" => array (
                "name" => 'Account_name_' . mt_rand(),
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
        $this->assertArrayHasKey('id', $result);

        $request['id'] = $result['id'];
        return $request;
    }

    /**
     * @param $request
     * @depends testCreate
     * @return array
     */
    public function testGet($request)
    {
        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_accounts')
        );
        $result = $this->client->getResponse();
        $result = ToolsAPI::jsonToArray($result->getContent());
        $id = $request['id'];
        $result = array_filter(
            $result,
            function ($a) use ($id) {
                return $a['id'] == $id;
            }
        );

        $this->assertNotEmpty($result);
        $this->assertEquals($request['account']['name'], reset($result)['name']);

        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_account', array('id' => $request['id']))
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $this->assertEquals($request['account']['name'], $result['name']);
    }

    /**
     * @param $request
     * @depends testCreate
     * @depends testGet
     */
    public function testUpdate($request)
    {
        $request['account']['name'] .= "_Updated";
        $this->client->request(
            'PUT',
            $this->client->generate('oro_api_put_account', array('id' => $request['id'])),
            $request
        );
        $result = $this->client->getResponse();

        ToolsAPI::assertJsonResponse($result, 204);

        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_account', array('id' => $request['id']))
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $this->assertEquals(
            $request['account']['name'],
            $result['name']
        );
    }

    /**
     * @param $request
     * @depends testCreate
     */
    public function testDelete($request)
    {
        $this->client->request(
            'DELETE',
            $this->client->generate('oro_api_delete_account', array('id' => $request['id']))
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);
        $this->client->request('GET', $this->client->generate('oro_api_get_account', array('id' => $request['id'])));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 404);
    }
}
