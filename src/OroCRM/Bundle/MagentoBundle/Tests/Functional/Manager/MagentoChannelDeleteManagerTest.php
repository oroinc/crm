<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Manager;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class MagentoChannelDeleteManagerTest extends WebTestCase
{
    /** @var int */
    protected static $channelId;

    /**
     * @var EntityManager
     */
    protected $em;

    protected function setUp()
    {
        $this->initClient(
            array(),
            array_merge($this->generateBasicAuthHeader(), array('HTTP_X-CSRF-Header' => 1))
        );
        $this->em = $this->client->getKernel()->getContainer()->get('doctrine.orm.entity_manager');

        $fixtures = ['OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel'];
        $this->loadFixtures($fixtures);
    }

    protected function postFixtureLoad()
    {
        $channel = $this->em->getRepository('OroIntegrationBundle:Channel')->findAll();
        $channel = reset($channel);
        if (!$channel) {
            $this->markTestIncomplete('Invalid fixtures, unable to perform test case');
        }

        self::$channelId = $channel->getId();
    }

    public function testDeleteChannel()
    {
        $channel   = $this->em->find('OroIntegrationBundle:Channel', self::$channelId);
        $channelId = $channel->getId();
        $this->assertGreaterThan(0, $this->getRecordsCount('OroCRMMagentoBundle:Cart', $channel));
        $this->assertGreaterThan(0, $this->getRecordsCount('OroCRMMagentoBundle:Order', $channel));
        $this->assertGreaterThan(0, $this->getRecordsCount('OroCRMMagentoBundle:Website', $channel));
        $this->client->getKernel()->getContainer()->get('oro_integration.channel_delete_manager')->deleteChannel(
            $channel
        );
        $this->assertEquals(0, $this->getRecordsCount('OroCRMMagentoBundle:Cart', $channelId));
        $this->assertEquals(0, $this->getRecordsCount('OroCRMMagentoBundle:Order', $channelId));
        $this->assertEquals(0, $this->getRecordsCount('OroCRMMagentoBundle:Website', $channelId));
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
            ->select('count(e)')
            ->from($repository, 'e')
            ->where('e.channel = :channel')
            ->setParameter('channel', $channel)
            ->getQuery()
            ->getOneOrNullResult();

        return array_shift($result);
    }
}
