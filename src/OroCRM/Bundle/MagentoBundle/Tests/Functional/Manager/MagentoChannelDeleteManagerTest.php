<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Manager;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

use Doctrine\Common\Util\Debug;

/**
 * @outputBuffering enabled
 * @db_isolation
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

    public function tearDown()
    {
        unset($this->client, $this->em);
    }

    public function getChannel()
    {
        return $this->em->getRepository('OroIntegrationBundle:Channel')->findOneByType('magento');
    }

    public function getEntity(Channel $channel, $repository)
    {
        return $this->em->getRepository($repository)->findByChannel($channel->getId());
    }

    public function testDeleteCart()
    {
        $channel = $this->getChannel();
        $entityName = 'OroCRMMagentoBundle:Cart';
        $entity  = $this->getEntity($channel, $entityName);

        $this->assertGreaterThan(0, count($entity));

        $this->client->getKernel()->getContainer()->get('oro_integration.channel_delete_manager')->deleteChannel($channel);

        $entity1 = $this->getEntity($channel, $entityName);

        $this->assertEquals(0, count($entity1));

        $this->assertGreaterThan(count($entity1), count($entity));

        unset($entity, $entity1);
    }

    public function testDeleteWebSite()
    {
        $channel = $this->getChannel();
        $entityName = 'OroCRMMagentoBundle:Website';

        $entity  = $this->getEntity($channel, $entityName);

        $this->assertGreaterThan(0, count($entity));

        $this->client->getKernel()->getContainer()->get('oro_integration.channel_delete_manager')->deleteChannel($channel);

        $entity1 = $this->getEntity($channel, $entityName);

        $this->assertEquals(0, count($entity1));
        $this->assertGreaterThan(count($entity1), count($entity));

        unset($entity, $entity1);
    }


}