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
     * @return array
     */
    public function testCreateAccount()
    {
        $request = array (
            "name" => 'Account_name_' . mt_rand(),
            //'group' => null,
            "owner" => '1',
        );

        $result = $this->client->getSoap()->createAccount($request);
        $this->assertTrue((bool) $result, $this->client->getSoap()->__getLastResponse());

        $request['id'] = $result;
        return $request;
    }

    /**
     * @param $request
     * @depends testCreateAccount
     * @return array
     */
    public function testGetAccounts($request)
    {
        $accounts = $this->client->getSoap()->getAccounts(1, 1000);
        $accounts = ToolsAPI::classToArray($accounts);
        $accountName = $request['name'];
        $account = $accounts['item'];
        if (isset($account[0])) {
            $account = array_filter($account, function($a) use ($accountName) { return $a['name'] == $accountName; });
            $account = reset($account);
        }

        $this->assertEquals($request['name'], $account['name']);
        $this->assertEquals($request['id'], $account['id']);
    }

    /**
     * @param $request
     * @depends testCreateAccount
     */
    public function testUpdateAccount($request)
    {
        $accountUpdate = $request;
        unset($accountUpdate['id']);
        $accountUpdate['name'] .= '_Updated';
        $result = $this->client->getSoap()->updateAccount($request['id'], $accountUpdate);
        $this->assertTrue($result);
        $account = $this->client->getSoap()->getAccount($request['id']);
        $account = ToolsAPI::classToArray($account);

        $this->assertEquals($accountUpdate['name'], $account['name']);

        return $request;
    }

    /**
     * @param $request
     * @depends testUpdateAccount
     * @throws \Exception|\SoapFault
     */
    public function testDeleteAccount($request)
    {
        $result = $this->client->getSoap()->deleteAccount($request['id']);
        $this->assertTrue($result);
        try {
            $this->client->getSoap()->getAccount($request['id']);
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
