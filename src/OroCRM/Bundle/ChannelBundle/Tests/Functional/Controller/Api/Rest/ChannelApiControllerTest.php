<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional\Api\Rest;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class ChannelApiControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());
        $this->loadFixtures(['OroCRM\Bundle\ChannelBundle\Tests\Functional\Fixture\LoadChannels']);
    }

    public function testCget()
    {
        $url = $this->getUrl('orocrm_api_get_channels');
        $this->client->request('GET', $url);

        $channels = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertNotEmpty($channels);
        $this->assertCount(2, $channels);
    }

    public function testCgetWithActiveFilter()
    {
        /** @var Channel $activeChannel */
        $activeChannel = $this->getReference('channel_1');

        /** @var Channel $inactiveChannel */
        $inactiveChannel = $this->getReference('channel_2');

        //fetch active channels
        $url = $this->getUrl('orocrm_api_get_channels', ['active' => 'false']);
        $this->client->request('GET', $url);

        $channels = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertNotEmpty($channels);
        $this->assertCount(1, $channels);
        $this->assertEquals($channels[0]['name'], $inactiveChannel->getName());

        //fetch inactive channels
        $url = $this->getUrl('orocrm_api_get_channels', ['active' => 'true']);
        $this->client->request('GET', $url);

        $channels = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertNotEmpty($channels);
        $this->assertCount(1, $channels);
        $this->assertEquals($channels[0]['name'], $activeChannel->getName());
    }

    public function testCgetWithEntityFilter()
    {
        $url = $this->getUrl(
            'orocrm_api_get_channels',
            ['entity' => 'OroCRM\Bundle\ChannelBundle\Entity\CustomerIdentity']
        );
        $this->client->request('GET', $url);

        $channels = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertNotEmpty($channels);
        $this->assertCount(1, $channels);
    }
}
