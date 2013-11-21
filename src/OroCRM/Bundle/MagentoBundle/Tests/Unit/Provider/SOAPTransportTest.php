<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider;

use OroCRM\Bundle\MagentoBundle\Provider\MageSoapTransport;

class SOAPTransportTest extends \PHPUnit_Framework_TestCase
{
    /** @var MageSoapTransport */
    protected $transport;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $soapClientMock;

    /** @var string */
    protected $sessionId;

    protected $settings = [
        'api_user' => 'user',
        'api_key'  => 'test',
    ];

    /**
     * Setup test entity
     */
    public function setUp()
    {
        $this->transport = $this->getMock(
            'OroCRM\Bundle\MagentoBundle\Provider\MageSoapTransport',
            ['getSoapClient']
        );

        $this->soapClientMock = $this->getMockBuilder('\SoapClient')
            ->disableOriginalConstructor()
            ->setMethods(['login', '__soapCall'])
            ->getMock();

        $this->transport->expects($this->once())
            ->method('getSoapClient')
            ->will($this->returnValue($this->soapClientMock));

        $this->soapClientMock->expects($this->once())
            ->method('login')
            ->with($this->settings['api_user'], $this->settings['api_key'])
            ->will($this->returnValue($this->sessionId));
    }

    /**
     * Tear down setup objects
     */
    public function tearDown()
    {
        unset($this->transport);
    }

    /**
     * Test init method
     */
    public function testInit()
    {
        $this->sessionId = uniqid();
        $settings = $this->settings;

        $result = $this->transport->init($settings);
        $this->assertFalse($result); // no wsdl_url param supplied

        $settings['wsdl_url'] = 'http://localhost/?wsdl'; // fake url

        $result = $this->transport->init($settings);
        $this->assertTrue($result);
    }

    /**
     * Test call method
     *
     * @depends testInit
     */
    public function testCall()
    {
        $action = 'testAction';

        $settings = $this->settings;
        $settings['wsdl_url'] = 'http://localhost/?wsdl'; // fake url

        $this->transport->init($settings);

        $this->soapClientMock->expects($this->once())
            ->method('__soapCall')
            ->with($action, [$this->sessionId, []])
            ->will($this->returnValue(true));

        $result = $this->transport->call($action);
        $this->assertTrue($result);
    }
}
