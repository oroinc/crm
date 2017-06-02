<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Transport;

use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestResponseInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use Oro\Bundle\IntegrationBundle\Test\FakeRestResponse;
use Oro\Bundle\MagentoBundle\Model\OroBridgeExtension\Config;
use Oro\Bundle\MagentoBundle\Provider\Transport\Provider\OroBridgeExtensionConfigProvider;

class OroBridgeExtensionConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var OroBridgeExtensionConfigProvider */
    protected $oroBridgeExtensionConfigProvider;

    /** @var  RestClientInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $client;

    /** @var  RestResponseInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $clientResponse;

    protected function setUp()
    {
        $this->client =  $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->oroBridgeExtensionConfigProvider = new OroBridgeExtensionConfigProvider();
        $this->oroBridgeExtensionConfigProvider->setClient($this->client);
    }

    public function testConfig()
    {
        $headers = [];

        $boby = json_encode([
            'version' => '1',
            'mage_version' => '1',
            'admin_url' => 'testAdminUrl',
            'customer_scope' => 'default'

        ]);

        $response = new FakeRestResponse(200, [], $boby);

        $this->client->expects($this->once())->method('get')->willReturn($response);

        $config = $this->oroBridgeExtensionConfigProvider->getConfig($headers, false);

        $expectedConfig = new Config();
        $expectedConfig->setAdminUrl('testAdminUrl');
        $expectedConfig->setCustomerScope('default');
        $expectedConfig->setExtensionVersion('1');
        $expectedConfig->setMagentoVersion('1');

        $this->assertEquals($config, $expectedConfig);
    }

    /**
     * @dataProvider getDataToTestForce
     *
     * @param array $body
     * @param int $expectedCountExecution
     * @param bool $force
     */
    public function testConfigForce($body, $expectedCountExecution, $force)
    {
        $headers = [];
        $response = new FakeRestResponse(200, [], json_encode($body));
        $this->client->expects($this->exactly($expectedCountExecution))->method('get')->willReturn($response);

        $expectedConfig = new Config();
        $expectedConfig->setAdminUrl('testAdminUrl');
        $expectedConfig->setCustomerScope('default');
        $expectedConfig->setExtensionVersion('1');
        $expectedConfig->setMagentoVersion('1');

        $config = $this->oroBridgeExtensionConfigProvider->getConfig($headers, false);
        $this->assertEquals($config, $expectedConfig);

        $config = $this->oroBridgeExtensionConfigProvider->getConfig($headers, $force);
        $this->assertEquals($config, $expectedConfig);
    }

    /**
     * @return array
     */
    public function getDataToTestForce()
    {
        return [
            'execute get config 2 times and second time with force = true' => [
                'body' => [
                    'version' => '1',
                    'mage_version' => '1',
                    'admin_url' => 'testAdminUrl',
                    'customer_scope' => 'default'
                ],
                'expectedCountExecution' => 2,
                'force' => true
            ],
            'execute get config 2 times and second time with force = false' => [
                'body' => [
                    'version' => '1',
                    'mage_version' => '1',
                    'admin_url' => 'testAdminUrl',
                    'customer_scope' => 'default'
                ],
                'expectedCountExecution' => 1,
                'force' => false
            ],
        ];
    }

    /**
     * @dataProvider getDataToTestException
     *
     * @param int $code
     */
    public function testConfigException($code)
    {
        $headers = [];

        $exception = new RestException('', $code);
        $this->client->expects($this->exactly(1))->method('get')->willThrowException($exception);

        if ($code !== 404) {
            $this->setExpectedException(RestException::class);
        }

        $config = $this->oroBridgeExtensionConfigProvider->getConfig($headers, false);

        if ($code === 404) {
            $expectedConfig = new Config();
            $this->assertEquals($config, $expectedConfig);
        }
    }

    /**
     * @return array
     */
    public function getDataToTestException()
    {
        return [
            'Error 404' => [
                'code' => 404
            ],
            'Error 500' => [
                'code' => 500
            ],
            'Error 401' => [
                'code' => 401
            ]
        ];
    }

    protected function tearDown()
    {
        unset($this->oroBridgeExtensionConfigProvider, $this->client);
    }
}
