<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\IntegrationBundle\Entity\Channel;

use OroCRM\Bundle\MagentoBundle\Tests\Unit\Stub\MemoryOutput;
use OroCRM\Bundle\MagentoBundle\Command\CartExpirationSyncCommand;

class CartExpirationSyncCommandTest extends \PHPUnit_Framework_TestCase
{
    /** @var CartExpirationSyncCommand */
    protected $command;

    /** @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $container;

    protected function setUp()
    {
        $this->command = new CartExpirationSyncCommand();

        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->command->setContainer($this->container);
    }

    protected function tearDown()
    {
        unset($this->container, $this->command);
    }

    public function testConfiguration()
    {
        $this->command->configure();

        $this->assertNotEmpty($this->command->getDescription());
        $this->assertNotEmpty($this->command->getName());
        $this->assertTrue($this->command->getDefinition()->hasOption('channel-id'));
    }

    public function testIsValidCronCommand()
    {
        $this->assertInstanceOf('Oro\Bundle\CronBundle\Command\CronCommandInterface', $this->command);

        $this->assertContains('oro:cron:', $this->command->getName(), 'name should start with oro:cron');
        $this->assertInternalType('string', $this->command->getDefaultDefinition());
    }

    public function testExecutionAlreadyRunningScenario()
    {
        $testChannelId = 11;

        /** @var CartExpirationSyncCommand|\PHPUnit_Framework_MockObject_MockObject $command */
        $command = $this->getMock('OroCRM\Bundle\MagentoBundle\Command\CartExpirationSyncCommand', ['isJobRunning']);
        $command->setContainer($this->container);

        $input  = new ArrayInput(['-c' => $testChannelId], $command->getDefinition());
        $output = new MemoryOutput();

        $command->expects($this->once())->method('isJobRunning')->with($testChannelId)
            ->will($this->returnValue(true));

        $command->execute($input, $output);

        $this->assertContains('Job already running', $output->getOutput());
    }

    /**
     * @dataProvider executionProvider
     *
     * @param Channel|Channel[]|null $result
     * @param int                    $expectedProcess
     * @param bool                   $singleChannel
     * @param null|string            $exception
     */
    public function testExecution($result, $expectedProcess, $singleChannel = true, $exception = null)
    {
        $testChannelId = 11;
        $params        = $singleChannel ? ['-c' => $testChannelId] : [];

        if (null !== $exception) {
            $this->setExpectedException($exception);
        }

        /** @var CartExpirationSyncCommand|\PHPUnit_Framework_MockObject_MockObject $command */
        $command = $this->getMock('OroCRM\Bundle\MagentoBundle\Command\CartExpirationSyncCommand', ['isJobRunning']);
        $command->setContainer($this->container);

        $input     = new ArrayInput($params, $command->getDefinition());
        $output    = new MemoryOutput();
        $processor = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Provider\CartExpirationProcessor')
            ->disableOriginalConstructor()->getMock();
        $em        = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();
        $repo      = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository')
            ->disableOriginalConstructor()->getMock();

        $command->expects($this->once())->method('isJobRunning')->with($singleChannel ? $testChannelId : null)
            ->will($this->returnValue(false));

        $this->container->expects($this->at(0))->method('get')
            ->with('orocrm_magento.provider.cart_expiration_processor')
            ->will($this->returnValue($processor));
        $this->container->expects($this->at(1))->method('get')
            ->with('doctrine.orm.entity_manager')
            ->will($this->returnValue($em));

        $em->expects($this->once())->method('getRepository')->with('OroIntegrationBundle:Channel')
            ->will($this->returnValue($repo));

        $repo->expects($this->exactly((int)$singleChannel))->method('getOrLoadById')
            ->will($this->returnValue($result));

        $repo->expects($this->exactly((int)!$singleChannel))->method('getConfiguredChannelsForSync')
            ->will($this->returnValue($result));

        $processor->expects($this->exactly($expectedProcess))->method('process');

        $command->execute($input, $output);
        $this->assertContains('Completed', $output->getOutput());
    }

    /**
     * @return array
     */
    public function executionProvider()
    {
        $channel1 = new Channel();
        $channel1->setConnectors(['customer']);

        $channel2 = new Channel();
        $channel2->setConnectors(['customer', 'cart']);

        return [
            'Channel not found'                                  => [null, 0, true, '\InvalidArgumentException'],
            'Channel found, skip due to cart connector disabled' => [$channel1, 0, true],
            'Channel found, process'                             => [$channel2, 1, true],
            'No channels found'                                  => [[], 0, false],
            'Channels found, process one'                        => [[$channel1, $channel2], 1, false],
        ];
    }

    public function testExceptionOutput()
    {
        $channel = new Channel();
        $channel->setConnectors(['customer', 'cart']);
        /** @var CartExpirationSyncCommand|\PHPUnit_Framework_MockObject_MockObject $command */
        $command = $this->getMock('OroCRM\Bundle\MagentoBundle\Command\CartExpirationSyncCommand', ['isJobRunning']);
        $command->setContainer($this->container);

        $input     = new ArrayInput([], $command->getDefinition());
        $output    = new MemoryOutput();
        $processor = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Provider\CartExpirationProcessor')
            ->disableOriginalConstructor()->getMock();
        $em        = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();
        $repo      = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository')
            ->disableOriginalConstructor()->getMock();

        $this->container->expects($this->at(0))->method('get')
            ->with('orocrm_magento.provider.cart_expiration_processor')
            ->will($this->returnValue($processor));
        $this->container->expects($this->at(1))->method('get')
            ->with('doctrine.orm.entity_manager')
            ->will($this->returnValue($em));
        $em->expects($this->once())->method('getRepository')->with('OroIntegrationBundle:Channel')
            ->will($this->returnValue($repo));

        $repo->expects($this->once())->method('getConfiguredChannelsForSync')
            ->will($this->returnValue([$channel]));

        $errorMessage = 'testErrorMessage';

        $processor->expects($this->once())->method('process')
            ->will($this->throwException(new \Exception($errorMessage)));

        $command->execute($input, $output);
        $this->assertContains($errorMessage, $output->getOutput());
    }
}
