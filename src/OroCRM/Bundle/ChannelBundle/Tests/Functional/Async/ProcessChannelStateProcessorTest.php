<?php
namespace OroCRM\Bundle\ChannelBundle\Tests\Functional\Async;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\ChannelBundle\Async\ProcessChannelStateProcessor;

/**
 * @dbIsolationPerTest
 */
class ProcessChannelStateProcessorTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient();
    }
    
    public function testCouldBeGetFromContainerAsService()
    {
        $processor = $this->getContainer()->get('orocrm_channel.async.process_channel_state_processor');
        
        $this->assertInstanceOf(ProcessChannelStateProcessor::class, $processor);
    }
}
