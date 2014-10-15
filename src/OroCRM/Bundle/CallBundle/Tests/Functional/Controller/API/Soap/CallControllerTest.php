<?php

namespace OroCRM\Bundle\CallBundle\Tests\Functional\Controller\API\Soap;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\CallBundle\Tests\Functional\DataFixtures\LoadCallDataFixtures;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class CallControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient(array(), $this->generateWsseAuthHeader());
        $this->loadFixtures(['OroCRM\Bundle\CallBundle\Tests\Functional\DataFixtures\LoadCallDataFixtures']);
        $this->initSoapClient();
    }

    /**
     * @return array
     */
    public function testCreate()
    {
        $request = array (
            "subject"       => 'Call Subject ' . mt_rand(),
            "phoneNumber"   => mt_rand(),
            "callStatus"    => LoadCallDataFixtures::STATUS_NAME,
            "direction"     => LoadCallDataFixtures::DIRECTION_NAME,

        );
        $result = $this->soapClient->createCall($request);

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
        $call = $this->soapClient->getCall($request['id']);
        $call = $this->valueToArray($call);
        $this->assertEquals($request['subject'], $call['subject']);
    }

    /**
     * @param array $request
     * @depends testCreate
     * @return array
     */
    public function testUpdate(array $request)
    {
        $callUpdate = $request;
        unset($callUpdate['id']);
        $callUpdate['subject'] .= ' Updated';

        $result = $this->soapClient->updateCall($request['id'], $callUpdate);
        $this->assertTrue($result);

        $call = $this->soapClient->getCall($request['id']);
        $call = $this->valueToArray($call);

        $this->assertEquals($callUpdate['subject'], $call['subject']);

        return $request;
    }

    /**
     * @param array $request
     * @depends testUpdate
     */
    public function testDelete(array $request)
    {
        $result = $this->soapClient->deleteCall($request['id']);
        $this->assertTrue($result);

        $this->setExpectedException('\SoapFault', 'Record with ID "' . $request['id'] . '" can not be found');
        $this->soapClient->getCall($request['id']);
    }
}
