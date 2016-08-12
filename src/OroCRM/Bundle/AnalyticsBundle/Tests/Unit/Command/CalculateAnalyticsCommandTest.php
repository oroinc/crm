<?php
namespace OroCRM\Bundle\AnalyticsBundle\Tests\Unit\Command;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Component\Testing\ClassExtensionTrait;
use OroCRM\Bundle\AnalyticsBundle\Command\CalculateAnalyticsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class CalculateAnalyticsCommandTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldBeSubClassOfCommand()
    {
        $this->assertClassExtends(Command::class, CalculateAnalyticsCommand::class);
    }

    public function testShouldImplementCronCommandInterface()
    {
        $this->assertClassImplements(CronCommandInterface::class, CalculateAnalyticsCommand::class);
    }

    public function testShouldImplementContainerAwareInterface()
    {
        $this->assertClassImplements(ContainerAwareInterface::class, CalculateAnalyticsCommand::class);
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new CalculateAnalyticsCommand();
    }

    public function testShouldBeExecutedEveryMidNightByCron()
    {
        $command = new CalculateAnalyticsCommand();

        $this->assertEquals('0 0 * * *', $command->getDefaultDefinition());
    }

    public function testShouldAllowSetContainer()
    {
        $container = new Container();

        $command = new CalculateAnalyticsCommand();

        $command->setContainer($container);

        $this->assertAttributeSame($container, 'container', $command);
    }
}
