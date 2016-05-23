<?php
namespace OroCRM\Bundle\ChannelBundle\Tests\Functional\Async;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\ChannelBundle\Async\ChangeIntegrationStatusProcessor;

/**
 * @dbIsolationPerTest
 */
class ChangeIntegrationStatusProcessorTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient();
    }
    
    public function testCouldBeGetFromContainerAsService()
    {
        $processor = $this->getContainer()->get('orocrm_channel.async.change_integration_status_processor');
        
        $this->assertInstanceOf(ChangeIntegrationStatusProcessor::class, $processor);
    }
}
