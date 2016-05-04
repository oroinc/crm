<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider;

use Doctrine\ORM\Query\Expr;

use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use OroCRM\Bundle\MagentoBundle\Provider\Connector\InitialNewsletterSubscriberConnector;
use OroCRM\Bundle\MagentoBundle\Provider\NewsletterSubscriberInitialSyncProcessor;
use OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider\Stub\InitialConnector;

class NewsletterSubscriberInitialSyncProcessorTest extends AbstractSyncProcessorTest
{
    /** @var NewsletterSubscriberInitialSyncProcessor */
    protected $processor;

    protected function setUp()
    {
        parent::setUp();

        $this->processor = new NewsletterSubscriberInitialSyncProcessor(
            $this->registry,
            $this->processorRegistry,
            $this->jobExecutor,
            $this->typesRegistry,
            $this->eventDispatcher,
            $this->logger,
            ['sync_settings' => ['initial_import_step_interval' => '2 days']]
        );

        $this->processor->setChannelClassName('Oro\IntegrationBundle\Entity\Channel');
    }

    public function testProcess()
    {
        $connectors = ['testConnector_initial'];
        $syncStartDate = new \DateTime('now', new \DateTimeZone('UTC'));

        $realConnector = new InitialConnector();
        $integration = $this->getIntegration($connectors, $syncStartDate, $realConnector);

        $transport = new MagentoSoapTransport();
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
