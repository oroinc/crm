<?php

namespace OroCRM\Bundle\AccountBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class RestAccountTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient(array(), $this->generateWsseAuthHeader());
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
            $this->getUrl('oro_api_post_account'),
            $request
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 201);
        $this->assertArrayHasKey('id', $result);

        $request['id'] = $result['id'];
        return $request;
    }

    /**
     * @param array $request
     * @depends testCreate
     * @return array
     */
    public function testGet(array $request)
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_accounts')
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

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
            $this->getUrl('oro_api_get_account', array('id' => $request['id']))
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals($request['account']['name'], $result['name']);
        $this->assertTrue(array_key_exists('lifetimeValue', $result));
    }

    /**
     * @param array $request
     * @depends testCreate
     * @depends testGet
     */
    public function testUpdate(array $request)
    {
        $request['account']['name'] .= "_Updated";
        $this->client->request(
            'PUT',
            $this->getUrl('oro_api_put_account', array('id' => $request['id'])),
            $request
        );
        $result = $this->client->getResponse();

        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_account', array('id' => $request['id']))
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals(
            $request['account']['name'],
            $result['name']
        );
    }

    /**
     * @param array $request
     * @depends testCreate
     */
    public function testList($request)
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_accounts')
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertEquals(1, count($result));
    }

    /**
     * @param array $request
     * @depends testCreate
     * @depends testList
     */
    public function testDelete(array $request)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl('oro_api_delete_account', array('id' => $request['id']))
        );
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);
        $this->client->request('GET', $this->getUrl('oro_api_get_account', array('id' => $request['id'])));
        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 404);
    }
}
