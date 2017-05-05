<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use PSR\Log\LoggerInterface;

use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestResponseInterface;
use Oro\Bundle\IntegrationBundle\Test\FakeRestClient;
use Oro\Bundle\IntegrationBundle\Test\FakeRestResponse;

use Oro\Bundle\MagentoBundle\Provider\RestPingProvider;


class RestPingProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject| RestClientInterface */
    protected $client;

    protected $response;

    /** @var  \PHPUnit_Framework_MockObject_MockObject| LoggerInterface */
    protected $logger;

    /** @var  RestPingProvider */
    protected $provider;

    /** @var  string */
    protected $rawBody;

    public function setUp()
    {
        $this->client = new FakeRestClient();

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
                             ->setMethods([
                                 'emergency',
                                 'alert',
                                 'critical',
                                 'error',
                                 'warning',
                                 'notice',
                                 'info',
                                 'debug',
                                 'log'
                             ])
                             ->getMock();

        $this->rawBody = '{"version":"0.1.2","mage_version":"2.1.4","admin_url":"http:\/\/fakemagento.local\/admin\/admin\/","customer_scope":"1"}';

        $this->provider = new RestPingProvider();
        $this->provider->setLogger($this->logger);
        $this->provider->setClient($this->client);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset(
            $this->client,
            $this->logger,
            $this->provider
        );
    }

    /**
     * @expectedException Oro\Bundle\MagentoBundle\Exception\RuntimeException
     * @expectedExceptionMessage REST Transport isn't configured properly.
     */
    public function testGetDataWithoutClient()
    {
        $provider = new RestPingProvider();
        $provider->setLogger($this->logger);
        $provider->isExtensionInstalled();
    }

    public function testSuccessPing()
    {
        $this->client->setDefaultResponse(new FakeRestResponse(200, [], ''));

        $this->assertTrue($this->provider->ping());
    }

    public function testFailurePing()
    {
        $this->client->setDefaultResponse(new FakeRestResponse(500, [], ''));

        $this->assertFalse($this->provider->ping());
    }

    public function testGetMagentoVersion()
    {
        $this->client->setDefaultResponse(
            new FakeRestResponse(200, [], $this->rawBody)
        );

        $this->assertNotEmpty($this->provider->getMagentoVersion());
        $this->assertEquals('2.1.4', $this->provider->getMagentoVersion());
    }

    public function testGetBridgeVersion()
    {
        $this->client->setDefaultResponse(
            new FakeRestResponse(200, [], $this->rawBody)
        );

        $this->assertNotEmpty($this->provider->getBridgeVersion());
        $this->assertEquals('0.1.2', $this->provider->getBridgeVersion());
    }

    public function testGetAdminUrl()
    {
        $this->client->setDefaultResponse(
            new FakeRestResponse(200, [], $this->rawBody)
        );

        $this->assertNotEmpty($this->provider->getAdminUrl());
        $this->assertEquals('http://fakemagento.local/admin/admin/', $this->provider->getAdminUrl());
    }

    public function testGetCustomerScope()
    {
        $this->client->setDefaultResponse(
            new FakeRestResponse(200, [], $this->rawBody)
        );

        $this->assertNotEmpty($this->provider->getCustomerScope());
        $this->assertEquals('1', $this->provider->getCustomerScope());
        $this->assertTrue($this->provider->isCustomerSharingPerWebsite());
    }

    public function testIsExtensionInstalled()
    {
        $this->client->setDefaultResponse(
            new FakeRestResponse(404, [], '')
        );

        $this->assertFalse($this->provider->isExtensionInstalled());

        $this->client->setDefaultResponse(
            new FakeRestResponse(200, [], $this->rawBody)
        );

        $this->provider->forceRequest();

        $this->assertTrue($this->provider->isExtensionInstalled());
    }
}
