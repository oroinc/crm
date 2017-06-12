<?php

namespace OroCRM\Bundle\CallBundle\Tests\Functional\Controller\API\Soap;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
 * @group soap
 */
class CallControllerTest extends WebTestCase
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
            "owner"         => '1',
            "subject"       => 'Call Subject ' . mt_rand(),
            "phoneNumber"   => mt_rand(),
            "notes"         => 'notes',
            "callDateTime"  => new \DateTime('now', new \DateTimeZone('UTC')),
            "callStatus"    => 'in_progress',
            "duration"      => new \DateTime('00:00:00', new \DateTimeZone('UTC')),
            "direction"     => 'incoming',
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
