<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\ImportExport\CustomerReverseSync;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolation
 */
class CustomerReverseSyncTest extends WebTestCase
{
    const FIXTURE_NS = 'OroCRM\\Bundle\\MagentoBundle\\Tests\\Functional\\ImportExport\\CustomerReverseSync\\Fixture\\';

    public function setUp()
    {
        $this->initClient();
        $fixtures = [
            self::FIXTURE_NS . 'TransportExtensionAwareFixture',
            self::FIXTURE_NS . 'ChannelFixture',
        ];

        $this->loadFixtures($fixtures);
    }

    public function test1()
    {
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        $channel = $em->find('OroIntegrationBundle:Channel', 1);
        $this->assertNotNull($channel);
    }
}
