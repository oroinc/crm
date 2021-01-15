<?php
namespace Oro\Bundle\AnalyticsBundle\Tests\Unit\Command;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\AnalyticsBundle\Command\CalculateAnalyticsCommand;
use Oro\Bundle\AnalyticsBundle\Service\CalculateAnalyticsScheduler;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Component\Testing\ClassExtensionTrait;
use Symfony\Component\Console\Command\Command;

class CalculateAnalyticsCommandTest extends \PHPUnit\Framework\TestCase
{
    use ClassExtensionTrait;

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $registry;

    /** @var CalculateAnalyticsScheduler|\PHPUnit\Framework\MockObject\MockObject */
    private $calculateAnalyticsScheduler;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->calculateAnalyticsScheduler = $this->createMock(CalculateAnalyticsScheduler::class);
    }

    public function testShouldBeSubClassOfCommand()
    {
        $this->assertClassExtends(Command::class, CalculateAnalyticsCommand::class);
    }

    public function testShouldImplementCronCommandInterface()
    {
        $this->assertClassImplements(CronCommandInterface::class, CalculateAnalyticsCommand::class);
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new CalculateAnalyticsCommand($this->registry, $this->calculateAnalyticsScheduler);
    }

    public function testShouldBeExecutedEveryMidNightByCron()
    {
        $command = new CalculateAnalyticsCommand($this->registry, $this->calculateAnalyticsScheduler);

        $this->assertEquals('0 0 * * *', $command->getDefaultDefinition());
    }
}
