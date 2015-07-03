<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional\Api\Rest;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class ChannelApiControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());
        $this->loadFixtures(['OroCRM\Bundle\ChannelBundle\Tests\Functional\Fixture\LoadChannel']);
    }

    public function testCget()
    {
        $url = $this->getUrl('orocrm_api_get_channels');
        $this->client->request('GET', $url);

        $channels = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertNotEmpty($channels);
        $this->assertCount(1, $channels);
    }
}
