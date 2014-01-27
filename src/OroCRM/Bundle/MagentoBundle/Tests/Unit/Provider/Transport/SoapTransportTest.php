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
    protected $sessionId = 'someId';

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
            ->setMethods(['__soapCall'])
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
    protected function initSettings($wsiMode = false)
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

        $params = ['username' => $testUsername, 'apiKey' => $this->decryptedApiKey];
        $params = $wsiMode ? [(object)$params] : $params;

        $result = $wsiMode ? (object)['result' => $this->sessionId] : $this->sessionId;

        $this->soapClientMock->expects($this->at(0))->method('__soapCall')
            ->with('login', $params)
            ->will($this->returnValue($result));
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
            ->with($action, ['sessionId' => $this->sessionId])
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
            ->with(SoapTransport::ACTION_PING, ['sessionId' => $this->sessionId])
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

        if ($throwsException) {
            $this->soapClientMock->expects($this->at(1))
                ->method('__soapCall')
                ->with(SoapTransport::ACTION_PING, ['sessionId' => $this->sessionId])
                ->will($this->throwException(new \Exception()));
        } else {
            $this->soapClientMock->expects($this->at(1))
                ->method('__soapCall')
                ->with(SoapTransport::ACTION_PING, ['sessionId' => $this->sessionId])
                ->will($this->returnValue($soapResult));
        }

        $this->transport->init($this->transportEntity);

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

    /**
     * @dataProvider wsiDataProvider
     *
     * @param mixed     $actionParams
     * @param mixed     $expectedParams
     * @param \stdClass $remoteResponse
     * @param mixed     $expectedData
     */
    public function testWSICompatibility($actionParams, $expectedParams, $remoteResponse, $expectedData)
    {
        $this->settings->set('wsi_mode', true);
        $this->initSettings(true);

        $testActionName = 'testAction';
        $this->soapClientMock->expects($this->at(1))->method('__soapCall')
            ->with($this->equalTo($testActionName), $this->equalTo([(object)$expectedParams]))
            ->will($this->returnValue($remoteResponse));

        $this->transport->init($this->transportEntity);

        $result = $this->transport->call($testActionName, $actionParams);
        $this->assertEquals($expectedData, $result);
    }

    /**
     * @return array
     */
    public function wsiDataProvider()
    {
        return [
            'returns bad result'                         => [
                [],
                (object)['sessionId' => $this->sessionId],
                false,
                null
            ],
            'returns single object'                      => [
                ['orderId' => 123],
                (object)['sessionId' => $this->sessionId, 'orderId' => 123],
                (object)['result' => (object)['entity_id' => 123, 'subtotal' => 123.32]],
                (object)['entity_id' => 123, 'subtotal' => 123.32]
            ],
            'returns collection of data'                 => [
                [],
                (object)['sessionId' => $this->sessionId],
                (object)[
                    'result' => (object)[
                            'complexObjectArray' => [
                                (object)[
                                    'entity_id' => 123,
                                    'subtotal'  => 123.32
                                ]
                            ]
                        ]
                ],
                [
                    (object)[
                        'entity_id' => 123,
                        'subtotal'  => 123.32
                    ]
                ]
            ],
            'returns single object in collection action' => [
                [],
                (object)['sessionId' => $this->sessionId],
                (object)[
                    'result' => (object)[
                            'complexObjectArray' =>
                                (object)[
                                    'entity_id' => 123,
                                    'subtotal'  => 123.32
                                ]

                        ]
                ],
                (object)[
                    'entity_id' => 123,
                    'subtotal'  => 123.32
                ]
            ],
            'returns single object with nested data'     => [
                ['orderId' => 123],
                (object)['sessionId' => $this->sessionId, 'orderId' => 123],
                (object)[
                    'result' => (object)[
                            'entity_id' => 123,
                            'subtotal'  => 123.32,
                            'items'     => (object)[
                                    'complexObjectArray' => [
                                        (object)[
                                            'parent_id' => 123,
                                            'id'        => 12
                                        ],
                                        (object)[
                                            'parent_id' => 123,
                                            'id'        => 13
                                        ]
                                    ]
                                ],
                        ]
                ],
                (object)[
                    'entity_id' => 123,
                    'subtotal'  => 123.32,
                    'items'     => [
                        (object)[
                            'parent_id' => 123,
                            'id'        => 12
                        ],
                        (object)[
                            'parent_id' => 123,
                            'id'        => 13
                        ]
                    ]
                ]
            ]
        ];
    }
}
