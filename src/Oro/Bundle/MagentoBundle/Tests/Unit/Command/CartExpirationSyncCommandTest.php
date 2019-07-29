<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Command;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\MagentoBundle\Command\SyncCartExpirationCommand;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\Testing\ClassExtensionTrait;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Command\Command;

class CartExpirationSyncCommandTest extends \PHPUnit\Framework\TestCase
{
    use ClassExtensionTrait;

    public function testShouldBeSubClassOfCommand()
    {
        $this->assertClassExtends(Command::class, SyncCartExpirationCommand::class);
    }

    public function testShouldImplementCronCommandInterface()
    {
        $this->assertClassImplements(CronCommandInterface::class, SyncCartExpirationCommand::class);
    }

    public function testShouldBeExecutedAtThreeOClockInTheMorningByCron()
    {
        /** @var RegistryInterface|\PHPUnit\Framework\MockObject\MockObject $doctrine */
        $doctrine = $this->createMock(RegistryInterface::class);

        /** @var MessageProducerInterface|\PHPUnit\Framework\MockObject\MockObject $messageProducer */
        $messageProducer = $this->createMock(MessageProducerInterface::class);

        $command = new SyncCartExpirationCommand($doctrine, $messageProducer);

        $this->assertEquals('0 3 * * *', $command->getDefaultDefinition());
    }
}
