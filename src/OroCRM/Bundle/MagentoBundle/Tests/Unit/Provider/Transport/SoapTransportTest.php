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

    protected function setUp()
    {
        $encoder = $this->getMock('Oro\Bundle\SecurityBundle\Encoder\Mcrypt');
        $encoder->expects($this->any())
            ->method('decryptData')
            ->with($this->encryptedApiKey)
            ->will($this->returnValue($this->decryptedApiKey));
        $wsdlManager = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Service\WsdlManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->transport = $this->getMock(
            'OroCRM\\Bundle\\MagentoBundle\\Provider\\Transport\\SoapTransport',
            ['getSoapClient'],
            [$encoder, $wsdlManager]
        );
        // Do not attempt to run request several times in Unit test. This leads to sleep and test performance impact
        $this->transport->setMultipleAttemptsEnabled(false);

        $this->soapClientMock = $this->getMockBuilder('\SoapClient')
            ->disableOriginalConstructor()
            ->getMock();

        $this->settings        = new ParameterBag();
        $this->transportEntity = $this->getMock('Oro\Bundle\IntegrationBundle\Entity\Transport');
        $this->transportEntity->expects($this->any())->method('getSettingsBag')
            ->will($this->returnValue($this->settings));
    }

    protected function tearDown()
    {
        unset($this->transport, $this->transportEntity, $this->settings, $this->encoder, $this->soapClientMock);
    }

    /**
     * Init settings bag
     * @param bool $wsiMode
     * @param array $functions
     */
    protected function initSettings($wsiMode = false, array $functions = [])
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

        $this->soapClientMock->expects($this->any())->method('__getFunctions')->willReturn($functions);
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
     * @param string $extensionVersion
     * @param bool   $expectedException
     */
    public function testResultIterators(
        $iteratorGetter,
        $expectedType,
        $extensionVersion = null,
        $expectedException = false
    ) {
        if (false !== $expectedException) {
            $this->setExpectedException($expectedException);
        }

        $this->initSettings(false, ['oroPing']);
        $this->transport->init($this->transportEntity);

        $this->soapClientMock->expects($this->any())
            ->method('__soapCall')
            ->with(SoapTransport::ACTION_PING, ['sessionId' => $this->sessionId])
            ->will($this->returnValue((object)['version' => $extensionVersion]));

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
                SoapTransport::REQUIRED_EXTENSION_VERSION
            ],
            'Carts with extension'                         => [
                'getCarts',
                'OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\CartsBridgeIterator',
                SoapTransport::REQUIRED_EXTENSION_VERSION
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
                SoapTransport::REQUIRED_EXTENSION_VERSION
            ],
            'Websites without extension'                   => [
                'getWebsites',
                'OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\WebsiteSoapIterator'
            ],
            'Websites with extension'                      => [
                'getWebsites',
                'OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\WebsiteSoapIterator',
                SoapTransport::REQUIRED_EXTENSION_VERSION
            ],
            'Stores without extension'                     => [
                'getStores',
                'OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\StoresSoapIterator'
            ],
            'Stores with extension'                        => [
                'getStores',
                'OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\StoresSoapIterator',
                SoapTransport::REQUIRED_EXTENSION_VERSION
            ],
            'Customer groups without extension'            => [
                'getCustomerGroups',
                'OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\CustomerGroupSoapIterator'
            ],
            'Customer groups with extension'               => [
                'getCustomerGroups',
                'OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\CustomerGroupSoapIterator',
                SoapTransport::REQUIRED_EXTENSION_VERSION
            ],
            'Customers without extension'                  => [
                'getCustomers',
                'OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\CustomerSoapIterator'
            ],
            'Customers with extension'                     => [
                'getCustomers',
                'OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\CustomerBridgeIterator',
                SoapTransport::REQUIRED_EXTENSION_VERSION
            ],
            'Newsletter Subscribers with extension' => [
                'getNewsletterSubscribers',
                'OroCRM\Bundle\MagentoBundle\Provider\Iterator\NewsletterSubscriberBridgeIterator',
                SoapTransport::REQUIRED_EXTENSION_VERSION
            ],
            'Newsletter Subscribers without extension' => [
                'getNewsletterSubscribers',
                'OroCRM\Bundle\MagentoBundle\Provider\Iterator\NewsletterSubscriberBridgeIterator',
                false,
                '\LogicException'
            ]
        ];
    }

    /**
     * @dataProvider isExtensionInstalledProvider
     *
     * @param array $functions
     * @param mixed $isInstalledResult
     * @param mixed $soapResult
     * @param mixed $adminUrlResult
     * @param bool|string $extensionVersion
     * @param bool|string $magentoVersion
     */
    public function testIsExtensionInstalled(
        array $functions,
        $isInstalledResult,
        $soapResult,
        $adminUrlResult = false,
        $extensionVersion = null,
        $magentoVersion = null
    ) {
        $this->initSettings(false, $functions);
        if ($functions) {
            $this->soapClientMock->expects($this->at(3))
                ->method('__soapCall')
                ->with(SoapTransport::ACTION_PING, ['sessionId' => $this->sessionId])
                ->will($this->returnValue($soapResult));
        }

        $this->transport->init($this->transportEntity);
        $result1 = $this->transport->isExtensionInstalled();
        $result2 = $this->transport->isExtensionInstalled();
        $this->assertSame($result1, $result2, 'All results should be same, and call remote service only once');
        $this->assertSame($isInstalledResult, $result1, 'Is installed is not correct');

        $result1 = $this->transport->getAdminUrl();
        $result2 = $this->transport->getAdminUrl();
        $this->assertSame($result1, $result2, 'All results should be same, and call remote service only once');
        $this->assertSame($adminUrlResult, $result1);

        $result1 = $this->transport->getExtensionVersion();
        $result2 = $this->transport->getExtensionVersion();
        $this->assertSame($result1, $result2, 'All results should be same, and call remote service only once');
        $this->assertSame($extensionVersion, $result1, 'Extension version is not correct');

        $result1 = $this->transport->getMagentoVersion();
        $result2 = $this->transport->getMagentoVersion();
        $this->assertSame($result1, $result2, 'All results should be same, and call remote service only once');
        $this->assertSame($magentoVersion, $result1, 'Magento version is not correct');
    }

    /**
     * @return array
     */
    public function isExtensionInstalledProvider()
    {
        return [
            'bridge is not installed because there is no oro function definitions' => [
                [],
                false,
                (object)[null],
            ],
            'good result with version' => [
                ['oroPing'],
                true,
                (object)[
                    'version' => '1.2.3',
                    'mage_version' => '1.8.0.0',
                    'admin_url' => 'http://localhost/admin/',
                ],
                'http://localhost/admin/',
                '1.2.3',
                '1.8.0.0',
            ],
            'good result with out version' => [
                ['oroPing'],
                false,
                (object)[null],
                false,
            ],
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
        $this->soapClientMock->expects($this->at(3))->method('__soapCall')
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

    /**
     * @dataProvider methodsDataProvider
     *
     * @param string $method
     * @param $endpoint
     * @param $expectedParameters
     * @param $result
     * @param array|null $arguments
     * @param bool $withPing
     * @param bool $extensionInstalled
     */
    public function testCalls(
        $method,
        $endpoint,
        $expectedParameters,
        $result,
        array $arguments = null,
        $withPing = false,
        $extensionInstalled = true
    ) {
        $this->initSettings(false, ['oroPing']);
        $this->transport->init($this->transportEntity);

        $this->soapClientMock->expects($withPing ? $this->at(1) : $this->once())
            ->method('__soapCall')
            ->with($endpoint, $expectedParameters)
            ->will($this->returnValue($result));

        $pingResponse = null;
        if ($withPing) {
            if ($extensionInstalled) {
                $pingResponse = (object)[
                    'version' => '1.2.3',
                    'mage_version' => '1.8.0.0',
                    'admin_url' => 'http://localhost/admin/'
                ];
            }

            $this->soapClientMock->expects($this->at(0))
                ->method('__soapCall')
                ->with(SoapTransport::ACTION_PING, ['sessionId' => $this->sessionId])
                ->will($this->returnValue($pingResponse));
        }

        $this->assertEquals($result, call_user_func_array([$this->transport, $method], $arguments));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function methodsDataProvider()
    {
        return [
            'getCustomerAddresses oro' => [
                'getCustomerAddresses',
                SoapTransport::ACTION_ORO_CUSTOMER_ADDRESS_LIST,
                ['sessionId' => $this->sessionId, 'customerId' => 1],
                [],
                [1],
                true,
                true
            ],
            'getCustomerAddresses native' => [
                'getCustomerAddresses',
                SoapTransport::ACTION_CUSTOMER_ADDRESS_LIST,
                ['sessionId' => $this->sessionId, 'customerId' => 1],
                [],
                [1],
                true,
                false
            ],
            'createCustomerAddress' => [
                'createCustomerAddress',
                SoapTransport::ACTION_CUSTOMER_ADDRESS_CREATE,
                ['sessionId' => $this->sessionId, 'customerId' => 3, 'addressData' => []],
                5,
                [3, []]
            ],
            'updateCustomerAddress' => [
                'updateCustomerAddress',
                SoapTransport::ACTION_CUSTOMER_ADDRESS_UPDATE,
                ['sessionId' => $this->sessionId, 'addressId' => 3, 'addressData' => []],
                true,
                [3, []]
            ],
            'getCustomerAddressInfo native' => [
                'getCustomerAddressInfo',
                SoapTransport::ACTION_CUSTOMER_ADDRESS_INFO,
                ['sessionId' => $this->sessionId, 'addressId' => 11],
                [],
                [11],
                true,
                false
            ],
            'getCustomerAddressInfo oro' => [
                'getCustomerAddressInfo',
                SoapTransport::ACTION_ORO_CUSTOMER_ADDRESS_INFO,
                ['sessionId' => $this->sessionId, 'addressId' => 11],
                [],
                [11],
                true,
                true
            ],
            'getCustomerInfo native' => [
                'getCustomerInfo',
                SoapTransport::ACTION_CUSTOMER_INFO,
                ['sessionId' => $this->sessionId, 'customerId' => 3],
                [],
                [3],
                true,
                false
            ],
            'getCustomerInfo oro' => [
                'getCustomerInfo',
                SoapTransport::ACTION_ORO_CUSTOMER_INFO,
                ['sessionId' => $this->sessionId, 'customerId' => 3],
                [],
                [3],
                true,
                true
            ],
            'createNewsletterSubscriber' => [
                'createNewsletterSubscriber',
                SoapTransport::ACTION_ORO_NEWSLETTER_SUBSCRIBER_CREATE,
                ['sessionId' => $this->sessionId, 'subscriberData' => []],
                [],
                [[]],
                true
            ],
            'updateNewsletterSubscriber' => [
                'updateNewsletterSubscriber',
                SoapTransport::ACTION_ORO_NEWSLETTER_SUBSCRIBER_UPDATE,
                ['sessionId' => $this->sessionId, 'subscriberId' => 3, 'subscriberData' => []],
                [],
                [3, []],
                true
            ],
            'getOrderInfo native' => [
                'getOrderInfo',
                SoapTransport::ACTION_ORDER_INFO,
                ['sessionId' => $this->sessionId, 'orderIncrementId' => 3],
                [],
                [3],
                true,
                false
            ],
            'getOrderInfo oro' => [
                'getOrderInfo',
                SoapTransport::ACTION_ORO_ORDER_INFO,
                ['sessionId' => $this->sessionId, 'orderIncrementId' => 3],
                [],
                [3],
                true,
                true
            ],
            'createCustomer native' => [
                'createCustomer',
                SoapTransport::ACTION_CUSTOMER_CREATE,
                ['sessionId' => $this->sessionId, 'customerData' => []],
                [],
                [[]],
                true,
                false
            ],
            'createCustomer oro' => [
                'createCustomer',
                SoapTransport::ACTION_ORO_CUSTOMER_CREATE,
                ['sessionId' => $this->sessionId, 'customerData' => []],
                [],
                [[]],
                true,
                true
            ],
            'updateCustomer native' => [
                'updateCustomer',
                SoapTransport::ACTION_CUSTOMER_UPDATE,
                ['sessionId' => $this->sessionId, 'customerId' => 3, 'customerData' => []],
                [],
                [3, []],
                true,
                false
            ],
            'updateCustomer oro' => [
                'updateCustomer',
                SoapTransport::ACTION_ORO_CUSTOMER_UPDATE,
                ['sessionId' => $this->sessionId, 'customerId' => 3, 'customerData' => []],
                [],
                [3, []],
                true,
                true
            ]
        ];
    }
}
