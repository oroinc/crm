<?php

namespace OroCRM\Bundle\AccountBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
 * @group soap
 */
class SoapAccountTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient(array(), $this->generateWsseAuthHeader());
        $this->initSoapClient();
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

        $result = $this->soapClient->createAccount($request);
        $this->assertTrue((bool) $result, $this->soapClient->__getLastResponse());

        $request['id'] = $result;
        return $request;
    }

    /**
     * @param array $request
     * @depends testCreate
     * @return array
     */
    public function testGet(array $request)
    {
        $accounts = $this->soapClient->getAccounts(1, 1000);
        $accounts = $this->valueToArray($accounts);
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
     * @param array $request
     * @depends testCreate
     */
    public function testUpdate(array $request)
    {
        $accountUpdate = $request;
        unset($accountUpdate['id']);
        $accountUpdate['name'] .= '_Updated';

        $result = $this->soapClient->updateAccount($request['id'], $accountUpdate);
        $this->assertTrue($result);

        $account = $this->soapClient->getAccount($request['id']);
        $account = $this->valueToArray($account);

        $this->assertEquals($accountUpdate['name'], $account['name']);

        return $request;
    }

    /**
     * @param array $request
     * @depends testUpdate
     */
    public function testDelete(array $request)
    {
        $result = $this->soapClient->deleteAccount($request['id']);
        $this->assertTrue($result);

        $this->setExpectedException('\SoapFault', 'Record with ID "' . $request['id'] . '" can not be found');
        $this->soapClient->getAccount($request['id']);
    }
}
