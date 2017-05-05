<?php
namespace Oro\Bundle\MagentoBundle\Tests\Functional\Provider\Transport;

use Oro\Bundle\IntegrationBundle\Test\FakeRestClientFactory;
use Oro\Bundle\MagentoBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\MagentoBundle\Exception\RuntimeException;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Provider\Transport\RestTransport;

/**
 * @dbIsolationPerTest
 */
class RestTransportTest extends WebTestCase
{
    /** @var FakeRestClientFactory */
    private $fakeRestClientFactory;

    public function setUp()
    {
        $this->initClient();

        $this->fakeRestClientFactory = new FakeRestClientFactory();

        $this->client->getContainer()->set(
            'oro_integration.transport.rest.client_factory.decorated',
            $this->fakeRestClientFactory
        );

    }

    public function testTransportInitSuccess()
    {
        $this->loadRestFixture('auth');

        $transportEntity = $this->getTransportEntity();
        /** @var RestTransport $transport */
        $transport = $this->client->getContainer()->get('oro_magento.transport.rest_transport');
        $transport->init($transportEntity);

        $this->assertEquals('fake_token', $transportEntity->getApiToken());
    }

    public function testTransportInitFailed()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Server returned unexpected response. Response code 500');

        $this->loadRestFixture('auth_error');

        /** @var RestTransport $transport */
        $transport = $this->client->getContainer()->get('oro_magento.transport.rest_transport');
        $transport->init($this->getTransportEntity());
    }

    public function testTransportInitInvalidCredentials()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(
            "Can't get token by defined 'api_key' and 'api_user'. Please check credentials !"
        );

        $this->loadRestFixture('auth_unauthorized');

        /** @var RestTransport $transport */
        $transport = $this->client->getContainer()->get('oro_magento.transport.rest_transport');
        $transport->init($this->getTransportEntity());
    }

    public function testPingSuccess()
    {
        $this->loadRestFixture('ping');

        /** @var RestTransport $transport */
        $transport = $this->client->getContainer()->get('oro_magento.transport.rest_transport');
        $transport->init($this->getTransportEntity());

        $this->assertTrue($transport->ping());
    }

    public function testExtensionDetection()
    {
        $this->loadRestFixture('ping');

        /** @var RestTransport $transport */
        $transport = $this->client->getContainer()->get('oro_magento.transport.rest_transport');
        $transport->init($this->getTransportEntity());

        $this->assertTrue($transport->isExtensionInstalled());
        $this->assertEquals("http://localhost/admin", $transport->getAdminUrl());
        $this->assertEquals("1.0.0", $transport->getExtensionVersion());
        $this->assertEquals("2.1.4", $transport->getMagentoVersion());
    }

    public function testNoOroExtension()
    {
        $this->loadRestFixture('ping_no_extension');

        /** @var RestTransport $transport */
        $transport = $this->client->getContainer()->get('oro_magento.transport.rest_transport');
        $transport->init($this->getTransportEntity());

        $this->assertFalse($transport->isExtensionInstalled());
    }

    /**
     * Creates transport entity
     *
     * @return MagentoTransport
     */
    private function getTransportEntity()
    {
        $transportEntity = new MagentoTransport();
        $transportEntity->setApiUrl('http://localhost');
        $transportEntity->setApiUser('admin');
        $transportEntity->setApiKey('admin123');
        $transportEntity->setApiToken(false);
        return $transportEntity;
    }

    /**
     * Initialize Fake REST client with fixtures
     *
     * @param string $fixtureName name of fixture
     */
    private function loadRestFixture($fixtureName)
    {
        $fixtureFile = sprintf(
            '%s/../../DataFixtures/response/%s.yml',
            __DIR__,
            $fixtureName
        );

        $fixtureFile = str_replace('/', DIRECTORY_SEPARATOR, $fixtureFile);
        $this->fakeRestClientFactory->setFixtureFile($fixtureFile);
    }
}
