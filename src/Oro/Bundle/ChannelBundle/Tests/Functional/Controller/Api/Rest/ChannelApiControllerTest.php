<?php

namespace Oro\Bundle\ChannelBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\CustomerIdentity;
use Oro\Bundle\ChannelBundle\Tests\Functional\Fixture\LoadChannels;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @group crm
 */
class ChannelApiControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateWsseAuthHeader());
        $this->loadFixtures([LoadChannels::class]);
    }

    public function testCget()
    {
        $url = $this->getUrl('oro_api_get_channels');
        $this->client->jsonRequest('GET', $url);

        $channels = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertNotEmpty($channels);
        $this->assertCount($this->getExpectedCountForCget(), $channels);
    }

    protected function getExpectedCountForCget(): int
    {
        return 2;
    }

    public function testCgetWithActiveFilter()
    {
        //fetch active channels
        $url = $this->getUrl('oro_api_get_channels', ['active' => 'false']);
        $this->client->jsonRequest('GET', $url);

        $channels = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertInactiveChannels($channels);

        //fetch inactive channels
        $url = $this->getUrl('oro_api_get_channels', ['active' => 'true']);
        $this->client->jsonRequest('GET', $url);

        $channels = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertActiveChannels($channels);
    }

    protected function assertInactiveChannels(array $channels): void
    {
        /** @var Channel $inactiveChannel */
        $inactiveChannel = $this->getReference('channel_2');

        $this->assertNotEmpty($channels);
        $this->assertCount(1, $channels);
        $this->assertEquals($channels[0]['name'], $inactiveChannel->getName());
    }

    protected function assertActiveChannels(array $channels): void
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
            ['entity' => CustomerIdentity::class]
        );
        $this->client->jsonRequest('GET', $url);

        $channels = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertNotEmpty($channels);
        $this->assertCount(1, $channels);
    }
}
