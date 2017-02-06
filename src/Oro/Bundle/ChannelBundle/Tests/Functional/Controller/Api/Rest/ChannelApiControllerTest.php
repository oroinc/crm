<?php

namespace Oro\Bundle\ChannelBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\ChannelBundle\Entity\Channel;

/**
 * @group crm
 */
class ChannelApiControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());
        $this->loadFixtures(['Oro\Bundle\ChannelBundle\Tests\Functional\Fixture\LoadChannels']);
    }

    public function testCget()
    {
        $url = $this->getUrl('oro_api_get_channels');
        $this->client->request('GET', $url);

        $channels = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertNotEmpty($channels);
        $this->assertCount($this->getExpectedCountForCget(), $channels);
    }

    /**
     * @return int
     */
    protected function getExpectedCountForCget()
    {
        return 2;
    }

    public function testCgetWithActiveFilter()
    {
        //fetch active channels
        $url = $this->getUrl('oro_api_get_channels', ['active' => 'false']);
        $this->client->request('GET', $url);

        $channels = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertInactiveChannels($channels);

        //fetch inactive channels
        $url = $this->getUrl('oro_api_get_channels', ['active' => 'true']);
        $this->client->request('GET', $url);

        $channels = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertActiveChannels($channels);
    }

    /**
     * @param array $channels
     */
    protected function assertInactiveChannels($channels)
    {
        /** @var Channel $inactiveChannel */
        $inactiveChannel = $this->getReference('channel_2');

        $this->assertNotEmpty($channels);
        $this->assertCount(1, $channels);
        $this->assertEquals($channels[0]['name'], $inactiveChannel->getName());
    }

    /**
     * @param array $channels
     */
    protected function assertActiveChannels($channels)
    {
        /** @var Channel $activeChannel */
        $activeChannel = $this->getReference('channel_1');

        $this->assertNotEmpty($channels);
        $this->assertCount(1, $channels);
        $this->assertEquals($channels[0]['name'], $activeChannel->getName());
    }

    public function testCgetWithEntityFilter()
    {
        $url = $this->getUrl(
            'oro_api_get_channels',
            ['entity' => 'Oro\Bundle\ChannelBundle\Entity\CustomerIdentity']
        );
        $this->client->request('GET', $url);

        $channels = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertNotEmpty($channels);
        $this->assertCount(1, $channels);
    }
}
