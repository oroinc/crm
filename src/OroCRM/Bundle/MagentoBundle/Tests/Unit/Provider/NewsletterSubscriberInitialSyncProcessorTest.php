<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider;

use Doctrine\ORM\Query\Expr;

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
        $this->processor->setSubscriberClassName('OroCRM\Bundle\MagentoBundle\Entity\NewsletterSubscriber');
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
        $this->repository->expects($this->atLeastOnce())
            ->method('getLastStatusForConnector')
            ->with($integration, InitialNewsletterSubscriberConnector::TYPE)
            ->will($this->returnValue($status));

        $this->assertProcessCalls();
        $this->assertExecuteJob(
            [
                'processorAlias' => false,
                'entityName' => 'testEntity',
                'channel' => 'testChannel',
                'channelType' => 'testChannelType',
                'initial_id' => 54321
            ]
        );

        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $qb->expects($this->once())
            ->method('select')
            ->will($this->returnSelf());

        $qb->expects($this->once())
            ->method('where')
            ->will($this->returnSelf());

        $qb->expects($this->once())
            ->method('expr')
            ->will($this->returnValue(new Expr()));

        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->setMethods(['getSingleScalarResult'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn(54321);

        $qb->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $this->repository->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($qb);

        $this->processor->process($integration);
    }
}
