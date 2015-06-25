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
        $this->loadFixtures(['OroCRM\Bundle\ChannelBundle\Tests\Functional\Controller\DataFixtures\LoadChannelData']);
    }

    public function testCget()
    {
        $url = $this->getUrl('orocrm_api_get_channels');
        $this->client->request('GET', $url);

        $response = $this->client->getResponse();
        $channels = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertNotEmpty($channels);
        $this->assertCount(3, $channels);

        $content = $response->getContent();
        $this->assertContains('Active', $content, 'Should contains channel with status "Active"');
        $this->assertContains('Inactive', $content, 'Should contains channel with status "Inactive"');
    }
}
