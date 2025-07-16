<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Async;

use Doctrine\DBAL\Exception\DeadlockException;
use Oro\Bundle\ContactBundle\Async\ContactPostImportProcessor;
use Oro\Bundle\ContactBundle\Handler\ContactEmailAddressHandler;
use Oro\Bundle\TestFrameworkBundle\Test\Logger\LoggerAwareTraitTestTrait;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\Message;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ContactPostImportProcessorTest extends TestCase
{
    use LoggerAwareTraitTestTrait;

    private ContactEmailAddressHandler&MockObject $contactEmailAddressHandler;
    private ContactPostImportProcessor $processor;

    #[\Override]
    protected function setUp(): void
    {
        $this->contactEmailAddressHandler = $this->createMock(ContactEmailAddressHandler::class);

        $this->processor = new ContactPostImportProcessor($this->contactEmailAddressHandler);
        $this->setUpLoggerMock($this->processor);
    }

    public function testProcessException(): void
    {
        $exception = new \Exception();
        $this->contactEmailAddressHandler->expects(self::once())
            ->method('actualizeContactEmailAssociations')
            ->willThrowException($exception);

        $this->expectException(\Exception::class);

        $this->processor->process(new Message(), $this->createMock(SessionInterface::class));
    }

    public function testProcessDeadlock(): void
    {
        $exception = $this->createMock(DeadlockException::class);
        $this->contactEmailAddressHandler->expects(self::once())
            ->method('actualizeContactEmailAssociations')
            ->willThrowException($exception);

        $this->assertLoggerErrorMethodCalled();

        self::assertEquals(
            MessageProcessorInterface::REQUEUE,
            $this->processor->process(new Message(), $this->createMock(SessionInterface::class))
        );
    }

    public function testProcessSuccess(): void
    {
        $this->contactEmailAddressHandler->expects(self::once())
            ->method('actualizeContactEmailAssociations');

        self::assertEquals(
            MessageProcessorInterface::ACK,
            $this->processor->process(new Message(), $this->createMock(SessionInterface::class))
        );
    }
}
