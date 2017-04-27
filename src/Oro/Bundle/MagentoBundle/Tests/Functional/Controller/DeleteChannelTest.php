<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Controller;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class DeleteChannelTest extends WebTestCase
{
    /**
     * @var Channel
     */
    protected $channel;

    public function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());

        $this->loadFixtures([LoadMagentoChannel::class]);
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
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_action_operation_execute',
                [
                    'operationName' => 'oro_integration_delete',
                    'entityId[id]' => $this->channel->getId(),
                    'entityClass' => get_class($this->channel),
                ]
            ),
            [],
            [],
            ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']
        );

        $this->assertJsonResponseStatusCodeEquals($this->client->getResponse(), 200);

        $this->assertNull($this->getChannel());
        $this->assertNull($this->getCartByChannel($this->channel));
        $this->assertNull($this->getOrderByChannel($this->channel));
        $this->assertNull($this->getCustomerByChannel($this->channel));
    }
}
