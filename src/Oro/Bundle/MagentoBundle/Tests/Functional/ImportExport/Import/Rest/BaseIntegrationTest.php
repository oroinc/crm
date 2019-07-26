<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\ImportExport\Import\Rest;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\IntegrationBundle\Test\FakeRestClientFactory;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

abstract class BaseIntegrationTest extends WebTestCase
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

        /**
         * Force transport re-initialization on every test
         */
        self::getContainer()
            ->get('oro_integration.provider.connector_context_mediator')
            ->resetInitializedTransport();
        $this->em = static::getContainer()->get('doctrine');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->em);
    }

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        self::$fakeRestClientFactory = new FakeRestClientFactory();
    }

    /**
     * {@inheritdoc}
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
