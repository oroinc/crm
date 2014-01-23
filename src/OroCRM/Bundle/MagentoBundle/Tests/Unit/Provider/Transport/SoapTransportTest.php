<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider\Transport;

use Symfony\Component\HttpFoundation\ParameterBag;

use Oro\Bundle\IntegrationBundle\Entity\Transport;

use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class SoapTransportTest extends \PHPUnit_Framework_TestCase
{
    /** @var SoapTransport|\PHPUnit_Framework_MockObject_MockObject */
    protected $transport;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $soapClientMock;

    /** @var Transport|\PHPUnit_Framework_MockObject_MockObject */
    protected $transportEntity;

    /** @var ParameterBag */
    protected $settings;

    /** @var string */
    protected $sessionId;

    /** @var string */
    protected $encryptedApiKey = 'encKey';

    /** @var string */
    protected $decryptedApiKey = 'api_key';

    public function setUp()
    {
        $encoder = $this->getMock('Oro\Bundle\SecurityBundle\Encoder\Mcrypt');
        $encoder->expects($this->any())
            ->method('decryptData')
            ->with($this->encryptedApiKey)
            ->will($this->returnValue($this->decryptedApiKey));

        $this->transport = $this->getMock(
            'OroCRM\\Bundle\\MagentoBundle\\Provider\\Transport\\SoapTransport',
            ['getSoapClient'],
            [$encoder]
        );

        $this->soapClientMock = $this->getMockBuilder('\SoapClient')
            ->disableOriginalConstructor()
            ->setMethods(['login', '__soapCall'])
            ->getMock();

        $this->settings        = new ParameterBag();
        $this->transportEntity = $this->getMock('Oro\Bundle\IntegrationBundle\Entity\Transport');
        $this->transportEntity->expects($this->any())->method('getSettingsBag')
            ->will($this->returnValue($this->settings));
    }

    public function tearDown()
    {
        unset($this->transport, $this->transportEntity, $this->settings, $this->encoder, $this->soapClientMock);
    }

    /**
     * Init settings bag
     */
    protected function initSettings()
    {
        $testUsername = 'apiUsername';

        $this->settings->set('api_key', $this->encryptedApiKey);
        $this->settings->set('api_user', $testUsername);
        $this->settings->set('website_id', 1);
        $this->settings->set('start_sync_date', new \DateTime());
        $this->settings->set('wsdl_url', 'http://localhost/?wsdl');

        $this->transport->expects($this->once())
            ->method('getSoapClient')
            ->will($this->returnValue($this->soapClientMock));

        $this->soapClientMock->expects($this->once())->method('login')
            ->with($testUsername, $this->decryptedApiKey)
            ->will($this->returnValue($this->sessionId));
    }


    /**
     * Test init method
     */
    public function testInit()
    {
        $this->sessionId = uniqid();
        $this->initSettings();

        $this->transport->init($this->transportEntity);
    }

    /**
     * Test init method errors
     */
    public function testInitErrors()
    {
        $this->sessionId = uniqid();

        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
        $this->settings->set('api_key', $this->encryptedApiKey);
        $this->settings->set('wsdl_url', 'http://localhost/?wsdl');
        $this->transport->init($this->transportEntity);
    }

    /**
     * Test call method
     */
    public function testCall()
    {
        $action = 'testAction';
        $this->initSettings();

        $this->transport->init($this->transportEntity);

        $this->soapClientMock->expects($this->once())
            ->method('__soapCall')
            ->with($action, [$this->sessionId])
            ->will($this->returnValue(true));

        $result = $this->transport->call($action);
        $this->assertTrue($result);

        $data = ['label', 'settingsFormType', 'settingsEntityFQCN'];
        foreach ($data as $item) {
            $this->assertNotEmpty($this->transport->{'get' . ucfirst($item)}(), $item . ' getter should not be empty');
        }
    }

    /**
     * @dataProvider iteratorsDataProvider
     *
     * @param string $iteratorGetter
     * @param string $expectedType
     * @param bool   $isExtensionInstalled
     * @param bool   $expectedException
     */
    public function testResultIterators(
        $iteratorGetter,
        $expectedType,
        $isExtensionInstalled = false,
        $expectedException = false
    ) {
        if (false !== $expectedException) {
            $this->setExpectedException($expectedException);
        }

        $this->initSettings();
        $this->transport->init($this->transportEntity);

        $this->soapClientMock->expects($this->any())
            ->method('__soapCall')
            ->with(SoapTransport::ACTION_PING, [$this->sessionId])
            ->will($this->returnValue((object)['version' => $isExtensionInstalled]));

        $result = $this->transport->{$iteratorGetter}();
        $this->assertInstanceOf($expectedType, $result);
    }

    /**
     * @return array
     */
    public function iteratorsDataProvider()
    {
        return [
            'Orders without extension'                     => [
                'getOrders',
                'OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\OrderSoapIterator'
            ],
            'Orders with extension'                        => [
                'getOrders',
                'OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\OrderBridgeIterator',
                true
            ],
            'Carts with extension'                         => [
                'getCarts',
                'OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\CartsBridgeIterator',
                true
            ],
            'Carts without extension should provoke error' => [
                'getCarts',
                'OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\CartsBridgeIterator',
                false,
                '\LogicException'
            ],
            'Regions without extension'                    => [
                'getRegions',
                'OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\RegionSoapIterator'
            ],
            'Regions with extension'                       => [
                'getRegions',
                'OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\RegionSoapIterator',
                true
            ],
            'Websites without extension'                   => [
                'getWebsites',
                'OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\WebsiteSoapIterator'
            ],
            'Websites with extension'                      => [
                'getWebsites',
                'OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\WebsiteSoapIterator',
                true
            ],
            'Stores without extension'                     => [
                'getStores',
                'OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\StoresSoapIterator'
            ],
            'Stores with extension'                        => [
                'getStores',
                'OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\StoresSoapIterator',
                true
            ],
            'Customer groups without extension'            => [
                'getCustomerGroups',
                'OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\CustomerGroupSoapIterator'
            ],
            'Customer groups with extension'               => [
                'getCustomerGroups',
                'OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\CustomerGroupSoapIterator',
                true
            ],
            'Customers without extension'                  => [
                'getCustomers',
                'OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\CustomerSoapIterator'
            ],
            'Customers with extension'                     => [
                'getCustomers',
                'OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\CustomerBridgeIterator',
                true
            ],
        ];
    }

    /**
     * @dataProvider isExtensionInstalledProvider
     *
     * @param bool  $expectedResult
     * @param mixed $soapResult
     * @param bool  $throwsException
     */
    public function testIsExtensionInstalled($expectedResult, $soapResult, $throwsException = false)
    {
        $this->initSettings();
        $this->transport->init($this->transportEntity);

        if ($throwsException) {
            $this->soapClientMock->expects($this->once())
                ->method('__soapCall')
                ->with(SoapTransport::ACTION_PING, [$this->sessionId])
                ->will($this->throwException(new \Exception()));
        } else {
            $this->soapClientMock->expects($this->once())
                ->method('__soapCall')
                ->with(SoapTransport::ACTION_PING, [$this->sessionId])
                ->will($this->returnValue($soapResult));
        }


        $result1 = $this->transport->isExtensionInstalled();
        $result2 = $this->transport->isExtensionInstalled();

        $this->assertSame($result1, $result2, 'All results should be same, and call remote service only once');
        $this->assertSame($expectedResult, $result1);
    }

    /**
     * @return array
     */
    public function isExtensionInstalledProvider()
    {
        return [
            'exception result is perceived as not installed' => [
                false,
                null,
                true
            ],
            'good result with version'                       => [
                true,
                (object)['version' => '1.2.3']
            ],
            'good result with out version'                   => [
                false,
                (object)[null]
            ]
        ];
    }
}
