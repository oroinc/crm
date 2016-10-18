<?php
namespace Oro\Bundle\MagentoBundle\Tests\Unit\Command;

use Oro\Component\Testing\ClassExtensionTrait;
use Oro\Bundle\MagentoBundle\Command\InitialSyncCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class InitialSyncCommandTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldBeSubClassOfCommand()
    {
        $this->assertClassExtends(Command::class, InitialSyncCommand::class);
    }

    public function testShouldImplementContainerAwareInterface()
    {
        $this->assertClassImplements(ContainerAwareInterface::class, InitialSyncCommand::class);
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new InitialSyncCommand();
    }

    public function testShouldAllowSetContainer()
    {
        $container = new Container();

        $command = new InitialSyncCommand();

        $command->setContainer($container);

        $this->assertAttributeSame($container, 'container', $command);
    }
}
