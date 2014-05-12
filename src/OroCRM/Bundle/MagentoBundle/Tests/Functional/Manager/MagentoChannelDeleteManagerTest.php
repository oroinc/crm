<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Manager;

use Doctrine\ORM\EntityManager;

use OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class MagentoChannelDeleteManagerTest extends WebTestCase
{

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var EntityManager
     */
    protected $em;

    public function setUp()
    {
        $this->client = static::createClient(
            array(),
            array_merge(ToolsAPI::generateBasicHeader(), array('HTTP_X-CSRF-Header' => 1))
        );
        $this->em = $this->client->getKernel()->getContainer()->get('doctrine.orm.entity_manager');
    }

    public function testDeleteChannel()
    {
        $channel = $this->getChannel();
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

    public function tearDown()
    {
        unset($this->client, $this->em);
    }

    /**
     * @return Channel
     */
    protected function getChannel()
    {
        $fixture = new LoadMagentoChannel();
        return $fixture->load($this->em);
    }

    /**
     * @param $repository
     * @param $channel
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
