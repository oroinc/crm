<?php

namespace OroCRM\Bundle\CallsBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class RestCallsTest extends WebTestCase
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
            "call" => array (
                "subject" => 'Test Call ' . mt_rand(),
                "owner" => '1',
                "duration" => '00:00:05',
                "direction" => 'outgoing',
                "callDateTime" => date('c'),
                "phoneNumber" => '123-123=123',
                "callStatus" => 'completed'
            )
        );
        $this->client->request(
            'POST',
            $this->client->generate('oro_api_post_call'),
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
     */
    public function testGet($request)
    {
        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_calls')
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
        $this->assertEquals($request['call']['subject'], reset($result)['subject']);

        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_call', array('id' => $id))
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        $this->assertEquals($request['call']['subject'], $result['subject']);
    }

    /**
     * @param $request
     * @depends testCreate
     * @depends testGet
     */
    public function testUpdate($request)
    {
        $request['call']['subject'] .= "_Updated";
        $this->client->request(
            'PUT',
            $this->client->generate('oro_api_put_call', array('id' => $request['id'])),
            $request
        );
        $result = $this->client->getResponse();

        ToolsAPI::assertJsonResponse($result, 204);

        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_call', array('id' => $request['id']))
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $this->assertEquals(
            $request['call']['subject'],
            $result['subject']
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
            $this->client->generate('oro_api_delete_call', array('id' => $request['id']))
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);
        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_call', array('id' => $request['id']))
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 404);
    }
}
