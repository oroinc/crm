<?php

namespace OroCRM\Bundle\AccountBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class SoapAccountTest extends WebTestCase
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
    public function testCreate()
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
     * @depends testCreate
     * @return array
     */
    public function testGet($request)
    {
        $accounts = $this->client->getSoap()->getAccounts(1, 1000);
        $accounts = ToolsAPI::classToArray($accounts);
        $accountName = $request['name'];
        $account = $accounts['item'];
        if (isset($account[0])) {
            $account = array_filter(
                $account,
                function ($a) use ($accountName) {
                    return $a['name'] == $accountName;
                }
            );
            $account = reset($account);
        }

        $this->assertEquals($request['name'], $account['name']);
        $this->assertEquals($request['id'], $account['id']);
    }

    /**
     * @param $request
     * @depends testCreate
     */
    public function testUpdate($request)
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
     * @depends testUpdate
     */
    public function testDelete($request)
    {
        $result = $this->client->getSoap()->deleteAccount($request['id']);
        $this->assertTrue($result);

        $this->setExpectedException('\SoapFault', 'Record with ID "' . $request['id'] . '" can not be found');
        $this->client->getSoap()->getAccount($request['id']);
    }
}
