<?php

namespace OroCRM\Bundle\AccountBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class RestAccountApiTest extends WebTestCase
{
    /** @var Client */
    protected $client;

    public function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());
    }

    public function testCreateAccount()
    {
        $request = array(
            "account" => array (
                "name" => 'Account_name_' . mt_rand(),
                "owner" => '1',
                "attributes" => array(
                    "website" => 'http://website.com',
                    "office_phone" => '123456789',
                    "description" => 'Account description',
                    "annual_revenue" => '100'
                )
            )
        );
        $this->client->request('POST', $this->client->generate('oro_api_post_account'), $request);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 201);

        return $request;
    }

    /**
     * @param $request
     * @depends testCreateAccount
     * @return array
     */
    public function testGetAccount($request)
    {
        $this->client->request('GET', $this->client->generate('oro_api_get_accounts'));
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

        $this->client->request('GET', $this->client->generate('oro_api_get_account', array('id' => $account['id'])));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        return $account;
    }

    /**
     * @param $account
     * @param $request
     * @depends testGetAccount
     * @depends testCreateAccount
     */
    public function testUpdateAccount($account, $request)
    {
        $request['account']['attributes']['description'] .= "_Updated";
        $this->client->request(
            'PUT',
            $this->client->generate('oro_api_put_account', array('id' => $account['id'])),
            $request
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);
        $this->client->request('GET', $this->client->generate('oro_api_get_account', array('id' => $account['id'])));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = json_decode($result->getContent(), true);
        $this->assertEquals(
            $request['account']['attributes']['description'],
            $result['attributes']['description']['value'],
            'Account does not updated'
        );
    }

    /**
     * @param $contact
     * @depends testGetAccount
     */
    public function testDeleteAccount($contact)
    {
        $this->client->request(
            'DELETE',
            $this->client->generate('oro_api_delete_account', array('id' => $contact['id']))
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);
        $this->client->request('GET', $this->client->generate('oro_api_get_account', array('id' => $contact['id'])));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 404);
    }
}
