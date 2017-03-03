<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\Customer;

class DeleteChannelTest extends WebTestCase
{
    /** @var Channel */
    protected $channel;

    public function setUp()
    {
        $this->initClient(array('debug' => false), $this->generateWsseAuthHeader());

        $this->loadFixtures(
            array(
                'Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel',
            )
        );
    }

    protected function postFixtureLoad()
    {
        $this->channel = $this->getChannel();
    }

    /**
     * @return Channel|null
     */
    protected function getChannel()
    {
        return $this->getContainer()
            ->get('doctrine')
            ->getRepository('OroIntegrationBundle:Channel')
            ->findOneByName('Demo Web store');
    }

    /**
     * @param Channel $channel
     *
     * @return Cart|null
     */
    protected function getCartByChannel(Channel $channel)
    {
        return $this->getContainer()
            ->get('doctrine')
            ->getRepository('OroMagentoBundle:Cart')
            ->findOneByChannel($channel);
    }

    /**
     * @param Channel $channel
     *
     * @return mixed
     */
    protected function getOrderByChannel(Channel $channel)
    {
        return $this->getContainer()
            ->get('doctrine')
            ->getRepository('OroMagentoBundle:Order')
            ->findOneByChannel($channel);
    }

    /**
     * @param Channel $channel
     *
     * @return Customer|null
     */
    protected function getCustomerByChannel(Channel $channel)
    {
        return $this->getContainer()
            ->get('doctrine')
            ->getRepository('OroMagentoBundle:Customer')
            ->findOneByChannel($channel);
    }

    public function testDeleteChannel()
    {
        $oldChannel = clone($this->channel);

        $this->client->request(
            'DELETE',
            $this->getUrl('oro_api_delete_integration', ['id' => $this->channel->getId()])
        );
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);
        $this->assertNull($this->getChannel());
        $this->assertNull($this->getCartByChannel($oldChannel));
        $this->assertNull($this->getOrderByChannel($oldChannel));
        $this->assertNull($this->getCustomerByChannel($oldChannel));
    }
}
