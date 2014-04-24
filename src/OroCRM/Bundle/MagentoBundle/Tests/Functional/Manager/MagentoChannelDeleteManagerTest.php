<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Manager;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

use Doctrine\ORM\EntityManager;

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

    public function testDelete()
    {
        $entity   = $this->getRepository('OroIntegrationBundle:Channel')->findOne();

        var_dump($entity);

        #$this->get('oro_integration.channel_delete_manager')->deleteChannel($entity);
    }

}