<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Async;

use Doctrine\DBAL\Driver\DriverException;
use Oro\Bundle\ContactBundle\Async\ContactPostImportProcessor;
use Oro\Bundle\ContactBundle\Handler\ContactEmailAddressHandler;
use Oro\Bundle\EntityBundle\ORM\DatabaseExceptionHelper;
use Oro\Component\MessageQueue\Job\Job;
use Oro\Component\MessageQueue\Job\JobStorage;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerInterface;

class ContactPostImportProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContactEmailAddressHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contactEmailAddressHandler;

    /**
     * @var DatabaseExceptionHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $databaseExceptionHelper;

    /**
     * @var JobStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jobStorage;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var ContactPostImportProcessor
     */
    private $processor;

    protected function setUp()
    {
        $this->contactEmailAddressHandler = $this->createMock(ContactEmailAddressHandler::class);
        $this->databaseExceptionHelper = $this->createMock(DatabaseExceptionHelper::class);
        $this->jobStorage = $this->createMock(JobStorage::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->processor = new ContactPostImportProcessor(
            $this->contactEmailAddressHandler,
            $this->databaseExceptionHelper,
            $this->jobStorage,
            $this->logger
        );
    }

    /**
     * @dataProvider invalidMessageDataProvider
     * @param mixed $messageBody
     */
    public function testProcessRejectUnsupportedMessage($messageBody)
    {
        /** @var MessageInterface|\PHPUnit_Framework_MockObject_MockObject $message */
        $message = $this->createMock(MessageInterface::class);
        /** @var SessionInterface|\PHPUnit_Framework_MockObject_MockObject $session */
        $session = $this->createMock(SessionInterface::class);

        $message->expects($this->once())
            ->method('getBody')
            ->willReturn(json_encode($messageBody));

        $this->assertEquals(ContactPostImportProcessor::REJECT, $this->processor->process($message, $session));
    }

    /**
     * @return array
     */
    public function invalidMessageDataProvider()
    {
        return [
            'null message' => [null],
            'incorrect message' => [''],
            'empty message' => [[]],
            'unsupported process' => [['process' => 'import_validate']]
        ];
    }

    public function testProcessNoParentJob()
    {
        /** @var MessageInterface|\PHPUnit_Framework_MockObject_MockObject $message */
        $message = $this->createMock(MessageInterface::class);
        /** @var SessionInterface|\PHPUnit_Framework_MockObject_MockObject $session */
        $session = $this->createMock(SessionInterface::class);

        $messageBody = [
            'process' => 'import',
            'rootImportJobId' => 42
        ];
        $message->expects($this->once())
            ->method('getBody')
            ->willReturn(json_encode($messageBody));

        $this->jobStorage->expects($this->once())
            ->method('findJobById')
            ->with(42)
            ->willReturn(null);

        $this->assertEquals(ContactPostImportProcessor::REJECT, $this->processor->process($message, $session));
    }

    /**
     * @dataProvider unsupportedJobNameDataProvider
     * @param string $jobName
     */
    public function testProcessParentJobUnsupportedImportType($jobName)
    {
        /** @var MessageInterface|\PHPUnit_Framework_MockObject_MockObject $message */
        $message = $this->createMock(MessageInterface::class);
        /** @var SessionInterface|\PHPUnit_Framework_MockObject_MockObject $session */
        $session = $this->createMock(SessionInterface::class);

        $messageBody = [
            'process' => 'import',
            'rootImportJobId' => 42
        ];
        $message->expects($this->once())
            ->method('getBody')
            ->willReturn(json_encode($messageBody));

        $job = new Job();
        $job->setName($jobName);
        $this->jobStorage->expects($this->once())
            ->method('findJobById')
            ->with(42)
            ->willReturn($job);

        $this->assertEquals(ContactPostImportProcessor::REJECT, $this->processor->process($message, $session));
    }

    /**
     * @return array
     */
    public function unsupportedJobNameDataProvider(): array
    {
        return [
            'non import' => ['test'],
            'import not contact' => ['oro:import:lead_import:csv:1']
        ];
    }

    public function testProcessException()
    {
        /** @var MessageInterface|\PHPUnit_Framework_MockObject_MockObject $message */
        $message = $this->createMock(MessageInterface::class);
        /** @var SessionInterface|\PHPUnit_Framework_MockObject_MockObject $session */
        $session = $this->createMock(SessionInterface::class);

        $messageBody = [
            'process' => 'import',
            'rootImportJobId' => 42
        ];
        $message->expects($this->once())
            ->method('getBody')
            ->willReturn(json_encode($messageBody));

        $job = new Job();
        $job->setName('oro:import:oro_contact.add_or_replace:csv:1');
        $this->jobStorage->expects($this->once())
            ->method('findJobById')
            ->with(42)
            ->willReturn($job);

        $exception = new \Exception();
        $this->contactEmailAddressHandler->expects($this->once())
            ->method('actualizeContactEmailAssociations')
            ->willThrowException($exception);

        $this->expectException(\Exception::class);

        $this->processor->process($message, $session);
    }

    public function testProcessDatabaseDriverException()
    {
        /** @var MessageInterface|\PHPUnit_Framework_MockObject_MockObject $message */
        $message = $this->createMock(MessageInterface::class);
        /** @var SessionInterface|\PHPUnit_Framework_MockObject_MockObject $session */
        $session = $this->createMock(SessionInterface::class);

        $messageBody = [
            'process' => 'import',
            'rootImportJobId' => 42
        ];
        $message->expects($this->once())
            ->method('getBody')
            ->willReturn(json_encode($messageBody));

        $job = new Job();
        $job->setName('oro:import:oro_contact.add_or_replace:csv:1');
        $this->jobStorage->expects($this->once())
            ->method('findJobById')
            ->with(42)
            ->willReturn($job);

        $exception = new \Exception();
        $databaseDriverExceprion = $this->createMock(DriverException::class);
        $this->contactEmailAddressHandler->expects($this->once())
            ->method('actualizeContactEmailAssociations')
            ->willThrowException(new \Exception);

        $this->databaseExceptionHelper->expects($this->once())
            ->method('getDriverException')
            ->with($exception)
            ->willReturn($databaseDriverExceprion);

        $this->databaseExceptionHelper->expects($this->once())
            ->method('isDeadlock')
            ->with($databaseDriverExceprion)
            ->willReturn(false);

        $this->expectException(\Exception::class);

        $this->processor->process($message, $session);
    }

    public function testProcessDeadlock()
    {
        /** @var MessageInterface|\PHPUnit_Framework_MockObject_MockObject $message */
        $message = $this->createMock(MessageInterface::class);
        /** @var SessionInterface|\PHPUnit_Framework_MockObject_MockObject $session */
        $session = $this->createMock(SessionInterface::class);

        $messageBody = [
            'process' => 'import',
            'rootImportJobId' => 42
        ];
        $message->expects($this->once())
            ->method('getBody')
            ->willReturn(json_encode($messageBody));

        $job = new Job();
        $job->setName('oro:import:oro_contact.add_or_replace:csv:1');
        $this->jobStorage->expects($this->once())
            ->method('findJobById')
            ->with(42)
            ->willReturn($job);

        $exception = new \Exception();
        $databaseDriverExceprion = $this->createMock(DriverException::class);
        $this->contactEmailAddressHandler->expects($this->once())
            ->method('actualizeContactEmailAssociations')
            ->willThrowException(new \Exception);

        $this->databaseExceptionHelper->expects($this->once())
            ->method('getDriverException')
            ->with($exception)
            ->willReturn($databaseDriverExceprion);

        $this->databaseExceptionHelper->expects($this->once())
            ->method('isDeadlock')
            ->with($databaseDriverExceprion)
            ->willReturn(true);

        $this->logger->expects($this->once())
            ->method('error');

        $this->assertEquals(ContactPostImportProcessor::REQUEUE, $this->processor->process($message, $session));
    }

    public function testProcessSuccess()
    {
        /** @var MessageInterface|\PHPUnit_Framework_MockObject_MockObject $message */
        $message = $this->createMock(MessageInterface::class);
        /** @var SessionInterface|\PHPUnit_Framework_MockObject_MockObject $session */
        $session = $this->createMock(SessionInterface::class);

        $messageBody = [
            'process' => 'import',
            'rootImportJobId' => 42
        ];
        $message->expects($this->once())
            ->method('getBody')
            ->willReturn(json_encode($messageBody));

        $job = new Job();
        $job->setName('oro:import:oro_contact.add_or_replace:csv:1');
        $this->jobStorage->expects($this->once())
            ->method('findJobById')
            ->with(42)
            ->willReturn($job);

        $exception = new \Exception();
        $databaseDriverExceprion = $this->createMock(DriverException::class);
        $this->contactEmailAddressHandler->expects($this->once())
            ->method('actualizeContactEmailAssociations');

        $this->assertEquals(ContactPostImportProcessor::ACK, $this->processor->process($message, $session));
    }
}
