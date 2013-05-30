<?php

namespace OroCRM\Bundle\AccounttBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class RestAccountApiTest extends WebTestCase
{
    public $client = null;

    public function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());
    }

    public function testCreateAccount()
    {
        $request = array(
            "account" => array (
                "name" => 'Account_name_' . mt_rand(),
                "attributes" => array(
                    "website" => 'http://website.com',
                    "office_phone" => '123456789',
                    "description" => 'Account description',
                    "annual_revenue" => '100'
                )
            )
        );
        $this->client->request('POST', 'http://localhost/api/rest/latest/account', $request);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 201);

        return $request;
    }

    /**
     * @param $request
     * @depends testCreateAccount
     * @return array
     */
    public function testGetContact($request)
    {
        $this->client->request('GET', 'http://localhost/api/rest/latest/accounts');
        $result = $this->client->getResponse();
        $result = json_decode($result->getContent(), true);
        $flag = 1;
        foreach ($result as $account) {
            if ($account['name'] == $request['account']['name']) {
                $flag = 0;
                break;
            }
        }
        $this->assertEquals(0, $flag);

        $this->client->request('GET', 'http://localhost/api/rest/latest/accounts' . '/' . $account['id']);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        return $account;
    }

    /**
     * @param $account
     * @param $request
     * @depends testGetContact
     * @depends testCreateAccount
     */
    public function testUpdateContact($account, $request)
    {
        $request['account']['attributes']['description'] .= "_Updated";
        $this->client->request('PUT', 'http://localhost/api/rest/latest/accounts' . '/' . $account['id'], $request);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);
        $this->client->request('GET', 'http://localhost/api/rest/latest/accounts' . '/' . $account['id']);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = json_decode($result->getContent(), true);
        $this->assertEquals($request['account']['attributes']['description'], $result['attributes']['description']['value'], 'Account does not updated');
    }

    /**
     * @param $contact
     * @depends testGetContact
     */
    public function testDeleteContact($contact)
    {
        $this->client->request('DELETE', 'http://localhost/api/rest/latest/accounts' . '/' . $contact['id']);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);
        $this->client->request('GET', 'http://localhost/api/rest/latest/accounts' . '/' . $contact['id']);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 404);
    }
}
