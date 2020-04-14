<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\EventListener;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Event\JobExecutionEvent;
use Akeneo\Bundle\BatchBundle\Item\ExecutionContext;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\EventListener\ImportEventListener;
use Oro\Bundle\PlatformBundle\Manager\OptionalListenerManager;

class ImportEventListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OptionalListenerManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $listenerManager;

    /**
     * @var ImportEventListener
     */
    private $listener;

    protected function setUp(): void
    {
        $this->listenerManager = $this->createMock(OptionalListenerManager::class);
        $this->listener = new ImportEventListener(
            $this->listenerManager
        );
    }

    public function testOnBeforeJobExecutionNotSupported()
    {
        $entityClass = \stdClass::class;
        $event = $this->getEvent($entityClass);
        $this->listenerManager->expects($this->never())
            ->method($this->anything());

        $this->listener->onBeforeJobExecution($event);
    }

    public function testOnBeforeJobExecutionSupported()
    {
        $entityClass = Contact::class;
        $event = $this->getEvent($entityClass);
        $this->listenerManager->expects($this->once())
            ->method('disableListener')
            ->with('oro_email.listener.entity_listener');

        $this->listener->onBeforeJobExecution($event);
    }

    public function testOnAfterJobExecutionNotSupported()
    {
        $entityClass = \stdClass::class;
        $event = $this->getEvent($entityClass);
        $this->listenerManager->expects($this->never())
            ->method($this->anything());

        $this->listener->onAfterJobExecution($event);
    }

    public function testOnAfterJobExecutionSupported()
    {
        $entityClass = Contact::class;
        $event = $this->getEvent($entityClass);
        $this->listenerManager->expects($this->once())
            ->method('enableListener')
            ->with('oro_email.listener.entity_listener');

        $this->listener->onAfterJobExecution($event);
    }

    /**
     * @param string $entityClass
     * @return JobExecutionEvent
     */
    private function getEvent($entityClass): JobExecutionEvent
    {
        /** @var ExecutionContext|\PHPUnit\Framework\MockObject\MockObject $context */
        $context = $this->createMock(ExecutionContext::class);
        $context->expects($this->any())
            ->method('get')
            ->with('entityName')
            ->willReturn($entityClass);

        /** @var JobExecution|\PHPUnit\Framework\MockObject\MockObject $jobExecution */
        $jobExecution = $this->createMock(JobExecution::class);
        $jobExecution->expects($this->any())
            ->method('getExecutionContext')
            ->willReturn($context);

        return new JobExecutionEvent($jobExecution);
    }
}
