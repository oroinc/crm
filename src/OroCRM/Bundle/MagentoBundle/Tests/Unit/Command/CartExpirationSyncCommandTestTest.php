<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Command;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Component\Testing\ClassExtensionTrait;
use OroCRM\Bundle\MagentoBundle\Command\SyncCartExpirationCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class CartExpirationSyncCommandTestTest extends \PHPUnit_Framework_TestCase
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

    public function testShouldImplementContainerAwareInterface()
    {
        $this->assertClassImplements(ContainerAwareInterface::class, SyncCartExpirationCommand::class);
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new SyncCartExpirationCommand();
    }

    public function testShouldBeExecutedAtThreeOClockInTheMorningByCron()
    {
        $command = new SyncCartExpirationCommand();

        $this->assertEquals('0 3 * * *', $command->getDefaultDefinition());
    }

    public function testShouldAllowSetContainer()
    {
        $container = new Container();

        $command = new SyncCartExpirationCommand();

        $command->setContainer($container);

        $this->assertAttributeSame($container, 'container', $command);
    }
}
