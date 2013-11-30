<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider;

use OroCRM\Bundle\MagentoBundle\Provider\MageSoapTransport;

class SOAPTransportTest extends \PHPUnit_Framework_TestCase
{
    /** @var MageSoapTransport */
    protected $transport;

    /** @var SoapClient|\PHPUnit_Framework_MockObject_MockObject */
    protected $soapClientMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $encoder;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $settings;

    /** @var string */
    protected $sessionId;

    /** @var string */
    protected $encryptedApiKey = 'encKey';

    /**
     * Setup test entity
     */
    public function setUp()
    {
        $this->encoder = $this->getMock('Oro\Bundle\SecurityBundle\Encoder\Mcrypt');
        $this->encoder->expects($this->once())
            ->method('decryptData')
            ->with($this->encryptedApiKey)
            ->will($this->returnValue('api_key'));

        $this->transport = $this->getMock(
            'OroCRM\Bundle\MagentoBundle\Provider\MageSoapTransport',
            ['getSoapClient'],
            [$this->encoder]
        );

        $this->soapClientMock = $this->getMockBuilder('\SoapClient')
            ->disableOriginalConstructor()
            ->setMethods(['login', '__soapCall'])
            ->getMock();

        $this->settings = $this->getMock('Symfony\Component\HttpFoundation\ParameterBag');
    }

    /**
     * Tear down setup objects
     */
    public function tearDown()
    {
        unset($this->transport, $this->encoder, $this->soapClientMock);
    }

    /**
     * Init settings bag
     */
    protected function initSettings()
    {
        $this->settings->expects($this->at(0))
            ->method('get')
            ->with('api_user')
            ->will($this->returnValue('api_user'));
        $this->settings->expects($this->at(1))
            ->method('get')
            ->with('api_key')
            ->will($this->returnValue($this->encryptedApiKey));

        $this->transport->expects($this->once())
            ->method('getSoapClient')
            ->will($this->returnValue($this->soapClientMock));

        $this->soapClientMock->expects($this->once())
            ->method('login')
            ->with('api_user', 'api_key')
            ->will($this->returnValue($this->sessionId));
    }


    /**
     * Test init method
     */
    public function testInit()
    {
        $this->sessionId = uniqid();
        $this->initSettings();

        $this->settings->expects($this->at(2))
            ->method('get')
            ->with('wsdl_url')
            ->will($this->returnValue('http://localhost/?wsdl'));

        $result = $this->transport->init($this->settings);
        $this->assertTrue($result);
    }

    /**
     * Test init method errors
     *
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testInitErrors()
    {
        $this->sessionId = uniqid();

        $this->settings->expects($this->at(1))
            ->method('get')
            ->with('api_key')
            ->will($this->returnValue($this->encryptedApiKey));

        $result = $this->transport->init($this->settings);
        $this->assertTrue($result);
    }

    /**
     * Test call method
     *
     */
    public function testCall()
    {
        $action = 'testAction';
        $this->initSettings();

        $this->settings->expects($this->at(2))
            ->method('get')
            ->with('wsdl_url')
            ->will($this->returnValue('http://localhost/?wsdl'));

        $this->transport->init($this->settings);

        $this->soapClientMock->expects($this->once())
            ->method('__soapCall')
            ->with($action, [$this->sessionId])
            ->will($this->returnValue(true));

        $result = $this->transport->call($action);
        $this->assertTrue($result);

        $data = ['label', 'settingsFormType', 'settingsEntityFQCN'];
        foreach ($data as $item) {
            $this->assertNotEmpty($this->transport->{'get'.ucfirst($item)}(), $item . ' getter should not be empty');
        }
    }
}
