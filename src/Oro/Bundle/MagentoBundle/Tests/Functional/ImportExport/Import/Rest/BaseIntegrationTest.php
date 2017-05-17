<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\ImportExport\Import\Rest;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\IntegrationBundle\Test\FakeRestClientFactory;

class BaseIntegrationTest extends WebTestCase
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var FakeRestClientFactory
     */
    protected static $fakeRestClientFactory;

    protected function setUp()
    {
        parent::setUp();
        $this->initClient();
        self::getContainer()->set(
            'oro_integration.transport.rest.client_factory.decorated',
            self::$fakeRestClientFactory
        );
        /**
         * Force transport re-initialization on every test
         */
        self::getContainer()
            ->get('oro_integration.provider.connector_context_mediator')
            ->resetInitializedTransport();
        $this->em = static::getContainer()->get('doctrine');
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        unset($this->em);
    }

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass()
    {
        self::$fakeRestClientFactory = new FakeRestClientFactory();
    }

    /**
     * @inheritDoc
     */
    public static function tearDownAfterClass()
    {
        self::$fakeRestClientFactory = null;
    }

    /**
     * @param string $fixtureName
     */
    protected function loadResponseFixture($fixtureName)
    {
        $fixtureFile = sprintf(
            '%s/response/%s.yml',
            __DIR__,
            $fixtureName
        );

        $fixtureFile = str_replace('/', DIRECTORY_SEPARATOR, $fixtureFile);
        self::$fakeRestClientFactory->setFixtureFile($fixtureFile);
    }
}
