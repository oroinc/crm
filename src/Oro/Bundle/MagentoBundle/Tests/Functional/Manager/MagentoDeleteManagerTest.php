<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Manager;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class MagentoDeleteManagerTest extends WebTestCase
{
    /** @var int */
    protected static $channelId;

    /**
     * @var EntityManager
     */
    protected $em;

    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->em = $this->client->getKernel()->getContainer()->get('doctrine.orm.entity_manager');

        $fixtures = ['Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel'];
        $this->loadFixtures($fixtures);
    }

    protected function postFixtureLoad()
    {
        $channel = $this->getReference('default_channel');
        if (!$channel) {
            $this->markTestIncomplete('Invalid fixtures, unable to perform test case');
        }

        self::$channelId = $channel->getId();
    }

    public function testDeleteChannel()
    {
        $channel   = $this->em->find('OroChannelBundle:Channel', self::$channelId);

        $integration = $channel->getDataSource();

        $integrationId = $integration->getId();

        $this->assertGreaterThan(0, $this->getRecordsCount('OroMagentoBundle:Cart', $integration));
        $this->assertGreaterThan(0, $this->getRecordsCount('OroMagentoBundle:Order', $integration));
        $this->assertGreaterThan(0, $this->getRecordsCount('OroMagentoBundle:Website', $integration));
        $this->client->getKernel()->getContainer()->get('oro_integration.delete_manager')->delete(
            $integration
        );
        $this->assertEquals(0, $this->getRecordsCount('OroMagentoBundle:Cart', $integrationId));
        $this->assertEquals(0, $this->getRecordsCount('OroMagentoBundle:Order', $integrationId));
        $this->assertEquals(0, $this->getRecordsCount('OroMagentoBundle:Website', $integrationId));
    }

    /**
     * @param $repository
     * @param $channel
     *
     * @return integer
     */
    protected function getRecordsCount($repository, $channel)
    {
        $result = $this->em->createQueryBuilder()
            ->select('COUNT(e)')
            ->from($repository, 'e')
            ->where('e.channel = :channel')
            ->setParameter('channel', $channel)
            ->getQuery()
            ->getOneOrNullResult();

        return array_shift($result);
    }
}
