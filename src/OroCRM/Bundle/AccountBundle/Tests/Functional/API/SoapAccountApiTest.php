<?php

namespace OroCRM\Bundle\AccountBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class SoapAccountApiTest extends WebTestCase
{
    /** @var Client */
    protected $client;

    public function setUp()
    {
        $this->markTestSkipped('BAP-717');
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());
        $this->client->soap(
            "http://localhost/api/soap",
            array(
                'location' => 'http://localhost/api/soap',
                'soap_version' => SOAP_1_2
            )
        );
    }

    /**
     * @param $request
     * @param $response
     * @dataProvider requestsApi
     * @return array
     */
    public function testCreateAccount($request, $response)
    {
        $result = $this->client->getSoap()->createAccount($request);
        ToolsAPI::assertEqualsResponse($response, $result, $this->client->getSoap()->__getLastResponse());

        return $request;
    }

    /**
     * @param $request
     * @dataProvider requestsApi
     * @depends testCreateAccount
     * @return array
     */
    public function testGetAccounts($request)
    {
        $accounts = $this->client->getSoap()->getAccounts(1, 1000);
        $accounts = ToolsAPI::classToArray($accounts);
        $result = false;
        foreach ($accounts as $account) {
            $result = $account['name'] == $request['name'];
            if ($result) {
                break;
            }
        }
        $this->assertTrue($result);
    }

    /**
     * @param $request
     * @param $response
     * @dataProvider requestsApi
     * @depends testCreateAccount
     * @return $accountId
     */
    public function testUpdateAccount($request, $response)
    {
        $accounts = $this->client->getSoap()->getAccounts(1, 1000);
        $accounts = ToolsAPI::classToArray($accounts);
        $result = false;
        foreach ($accounts as $account) {
            $result = $account['attributes']['description'] == $request['attributes']['description'];
            if ($result) {
                $accountId = $account['id'];
                break;
            }
        }
        $request['attributes']['description'] .= '_Updated';
        $result = $this->client->getSoap()->updateAccount($accountId, $request);
        $this->assertTrue($result);
        $account = $this->client->getSoap()->getAccount($accountId);
        $account = ToolsAPI::classToArray($account);
        $result = false;
        if ($account['attributes']['description'] == $request['attributes']['description']) {
            $result = true;
        }
        $this->assertTrue($result);

        return $accountId;
    }

    /**
     * @param $request
     * @param $response
     * @dataProvider requestsApi
     * @depends testUpdateAccount
     * @throws \Exception|\SoapFault
     */
    public function testDeleteAccount($request, $response)
    {
        $accounts = $this->client->getSoap()->getAccounts(1, 1000);
        $accounts = ToolsAPI::classToArray($accounts);
        $result = false;
        foreach ($accounts as $account) {
            $result = $account['name'] == $request['name']. '_Updated';
            if ($result) {
                $accountId = $account['id'];
                break;
            }
        }
        $result = $this->client->getSoap()->deleteAccount($accountId);
        $this->assertTrue($result);
        try {
            $this->client->getSoap()->getAccount($accountId);
        } catch (\SoapFault $e) {
            if ($e->faultcode != 'NOT_FOUND') {
                throw $e;
            }
        }
    }

    /**
     * Data provider for API tests
     * @return array
     */
    public function requestsApi()
    {
        return ToolsAPI::requestsApi(__DIR__ . DIRECTORY_SEPARATOR . 'Request');
    }
}
