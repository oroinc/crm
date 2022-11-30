<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\EventListener;

use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Event\JobExecutionEvent;
use Oro\Bundle\BatchBundle\Item\ExecutionContext;
use Oro\Bundle\ContactBundle\Async\Topic\ActualizeContactEmailAssociationsTopic;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\EventListener\ImportEventListener;
use Oro\Bundle\ContactBundle\ImportExport\Configuration\ContactImportExportConfigurationProvider;
use Oro\Bundle\PlatformBundle\Manager\OptionalListenerManager;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ImportEventListenerTest extends \PHPUnit\Framework\TestCase
{
    private OptionalListenerManager|\PHPUnit\Framework\MockObject\MockObject $optionalListenerManager;

    private MessageProducerInterface|\PHPUnit\Framework\MockObject\MockObject $messageProducer;

    private ImportEventListener $listener;

    protected function setUp(): void
    {
        $this->messageProducer = $this->createMock(MessageProducerInterface::class);
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects(self::any())
            ->method('trans')
            ->willReturn(static fn (string $key) => $key . '.translated');

        $contactImportExportConfigurationProvider = new ContactImportExportConfigurationProvider($translator);
        $this->optionalListenerManager = $this->createMock(OptionalListenerManager::class);

        $this->listener = new ImportEventListener(
            $this->optionalListenerManager,
            $contactImportExportConfigurationProvider,
            $this->messageProducer
        );
    }

    public function testOnBeforeJobExecutionNotSupportedEntityClass(): void
    {
        $entityClass = \stdClass::class;
        $event = $this->getEvent($entityClass, 'oro_contact.add_or_replace');
        $this->optionalListenerManager->expects(self::never())
            ->method(self::anything());

        $this->listener->onBeforeJobExecution($event);
    }

    public function testOnBeforeJobExecutionNotSupportedProcessorAliasClass(): void
    {
        $entityClass = Contact::class;
        $event = $this->getEvent($entityClass, 'invalid.processor.alias');
        $this->optionalListenerManager->expects(self::never())
            ->method(self::anything());

        $this->listener->onBeforeJobExecution($event);
    }

    public function testOnBeforeJobExecutionSupported(): void
    {
        $event = $this->getEvent(Contact::class, 'oro_contact.add_or_replace');
        $this->optionalListenerManager->expects(self::once())
            ->method('disableListener')
            ->with('oro_email.listener.entity_listener');

        $this->listener->onBeforeJobExecution($event);
    }

    public function testOnAfterJobExecutionNotSupportedEntityClass(): void
    {
        $event = $this->getEvent(\stdClass::class, 'oro_contact.add_or_replace');
        $this->optionalListenerManager->expects(self::never())
            ->method(self::anything());

        $this->listener->onAfterJobExecution($event);
    }

    public function testOnAfterJobExecutionNotSupportedProcessorAlias(): void
    {
        $event = $this->getEvent(Contact::class, 'invalid.processor.alias');
        $this->optionalListenerManager->expects(self::never())
            ->method(self::anything());

        $this->listener->onAfterJobExecution($event);
    }

    public function testOnAfterJobExecutionSupported(): void
    {
        $event = $this->getEvent(Contact::class, 'oro_contact.add_or_replace');
        $this->optionalListenerManager->expects(self::once())
            ->method('enableListener')
            ->with('oro_email.listener.entity_listener');

        $this->messageProducer->expects(self::once())
            ->method('send')
            ->with(ActualizeContactEmailAssociationsTopic::getName(), []);

        $this->listener->onAfterJobExecution($event);
    }

    private function getEvent(string $entityClass, string $processorAlias): JobExecutionEvent
    {
        $context = new ExecutionContext();
        $context->put('entityName', $entityClass);
        $context->put('processorAlias', $processorAlias);

        $jobExecution = new JobExecution();
        $jobExecution->setExecutionContext($context);

        return new JobExecutionEvent($jobExecution);
    }
}
