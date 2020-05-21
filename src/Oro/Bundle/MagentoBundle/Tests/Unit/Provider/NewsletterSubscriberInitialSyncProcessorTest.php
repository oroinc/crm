<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Oro\Bundle\MagentoBundle\Entity\MagentoRestTransport;
use Oro\Bundle\MagentoBundle\Provider\NewsletterSubscriberInitialSyncProcessor;
use Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Stub\InitialConnector;

class NewsletterSubscriberInitialSyncProcessorTest extends AbstractSyncProcessorTest
{
    /** @var NewsletterSubscriberInitialSyncProcessor */
    protected $processor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->processor = new NewsletterSubscriberInitialSyncProcessor(
            $this->registry,
            $this->processorRegistry,
            $this->jobExecutor,
            $this->typesRegistry,
            $this->eventDispatcher,
            $this->logger,
            ['sync_settings' => ['import_step_interval' => '2 days']]
        );

        $this->processor->setChannelClassName('Oro\IntegrationBundle\Entity\Channel');
    }

    public function testProcess()
    {
        $connectors = ['testConnector_initial'];
        $syncStartDate = new \DateTime('now', new \DateTimeZone('UTC'));

        $realConnector = new InitialConnector();
        $integration = $this->getIntegration($connectors, $syncStartDate, $realConnector);

        $status = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Status')
            ->disableOriginalConstructor()
            ->getMock();
        $status->expects($this->atLeastOnce())
            ->method('getData')
            ->will($this->returnValue([]));
        $this->repository->expects($this->any())
            ->method('getLastStatusForConnector')
            ->will($this->returnValue($status));

        $transport = new MagentoRestTransport();
        $transport->setNewsletterSubscriberSyncedToId(42);
        $integration->expects($this->any())
            ->method('getTransport')
            ->will($this->returnValue($transport));

        $this->assertProcessCalls();
        $this->assertExecuteJob(
            [
                'initialSyncInterval' => '7 days',
                'processorAlias' => false,
                'entityName' => 'testEntity',
                'channel' => 'testChannel',
                'channelType' => 'testChannelType',
                'initial_id' => 42
            ]
        );

        $this->processor->process($integration);
    }
}
