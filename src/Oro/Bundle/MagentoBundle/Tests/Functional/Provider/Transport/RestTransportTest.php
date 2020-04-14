<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Provider\Transport;

use Oro\Bundle\MagentoBundle\Entity\MagentoRestTransport;
use Oro\Bundle\MagentoBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\MagentoBundle\Exception\RuntimeException;
use Oro\Bundle\MagentoBundle\Provider\Transport\RestTransport;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class RestTransportTest extends WebTestCase
{
    /**
     * @var SymmetricCrypterInterface
     */
    protected $crypter;

    protected function setUp(): void
    {
        $this->initClient();

        $this->crypter = $this->client->getContainer()->get('oro_security.encoder.default');
    }

    public function testTransportInitSuccess()
    {
        $this->loadRestFixture('auth');

        $transportEntity = $this->getTransportEntity();
        /** @var RestTransport $transport */
        $transport = $this->client->getContainer()->get('oro_magento.transport.rest_transport');
        $transport->init($transportEntity);

        $this->assertEquals(
            'fake_token',
            $this->crypter->decryptData($transportEntity->getApiToken())
        );
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

    public function testExtensionDetectionWithoutResetInitialState()
    {
        $this->loadRestFixture('ping');

        /** @var RestTransport $transport */
        $transport = $this->client->getContainer()->get('oro_magento.transport.rest_transport');
        $transport->init($this->getTransportEntity());

        $this->assertFalse($transport->isExtensionInstalled());
        $this->assertEquals("http://localhost/admin", $transport->getAdminUrl());
        $this->assertEquals(null, $transport->getExtensionVersion());
        $this->assertEquals(null, $transport->getMagentoVersion());
    }

    public function testExtensionDetectionWithDataAndWithoutResetInitialState()
    {
        $this->loadRestFixture('ping');

        /** @var RestTransport $transport */
        $transport = $this->client->getContainer()->get('oro_magento.transport.rest_transport');
        $transport->init($this->getTransportEntity(true));

        $this->assertFalse($transport->isExtensionInstalled());
        $this->assertEquals("http://localhost/admin", $transport->getAdminUrl());
        $this->assertEquals("1.0.0", $transport->getExtensionVersion());
        $this->assertEquals("2.1.4", $transport->getMagentoVersion());
    }

    public function testExtensionDetectionWithResetInitialState()
    {
        $this->loadRestFixture('ping');

        /** @var RestTransport $transport */
        $transport = $this->client->getContainer()->get('oro_magento.transport.rest_transport');
        $transport->init($this->getTransportEntity());
        $transport->resetInitialState();
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
     * @param bool $withVersions adds extension version and magento version in $transportEntity
     *
     * @return MagentoRestTransport
     */
    private function getTransportEntity($withVersions = false)
    {
        $transportEntity = new MagentoRestTransport();
        $transportEntity->setApiUrl('http://localhost');
        $transportEntity->setApiUser('admin');
        $transportEntity->setApiKey('admin123');

        if ($withVersions) {
            $transportEntity->setExtensionVersion('1.0.0');
            $transportEntity->setMagentoVersion('2.1.4');
        }

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
        $this->getContainer()->get('oro_integration.transport.rest.client_factory.stub')->setFixtureFile($fixtureFile);
    }
}
