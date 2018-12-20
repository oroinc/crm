<?php

namespace Oro\Bundle\ActivityContactBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\ActivityContactBundle\DependencyInjection\Compiler\DirectionProviderPass;
use Symfony\Component\DependencyInjection\Reference;

class DirectionProviderPassTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $container;

    protected function setUp()
    {
        $this->container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')->getMock();
    }

    public function testProcessNotRegisterProvider()
    {
        $this->container->expects($this->once())
            ->method('hasDefinition')
            ->with($this->equalTo('oro_activity_contact.provider'))
            ->will($this->returnValue(false));

        $this->container->expects($this->never())
            ->method('getDefinition');
        $this->container->expects($this->never())
            ->method('findTaggedServiceIds');

        $compilerPass = new DirectionProviderPass();
        $compilerPass->process($this->container);
    }

    public function testProcess()
    {
        $definition = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->getMock();
        $definition->expects($this->at(0))
            ->method('addMethodCall')
            ->with(
                $this->equalTo('addProvider'),
                $this->equalTo([new Reference('provider4')])
            );
        $definition->expects($this->at(1))
            ->method('addMethodCall')
            ->with(
                $this->equalTo('addProvider'),
                $this->equalTo([new Reference('provider1')])
            );
        $definition->expects($this->at(2))
            ->method('addMethodCall')
            ->with(
                $this->equalTo('addProvider'),
                $this->equalTo([new Reference('provider2')])
            );
        $definition->expects($this->at(3))
            ->method('addMethodCall')
            ->with(
                $this->equalTo('addProvider'),
                $this->equalTo([new Reference('provider3')])
            );

        $serviceIds = [
            'provider1' => [['class' => 'Test\Class1']],
            'provider2' => [['class' => 'Test\Class2']],
            'provider3' => [['class' => 'Test\Class1', 'priority' => 100]],
            'provider4' => [['class' => 'Test\Class1', 'priority' => -100]],
        ];

        $this->container->expects($this->once())
            ->method('hasDefinition')
            ->with($this->equalTo('oro_activity_contact.provider'))
            ->will($this->returnValue(true));

        $this->container->expects($this->once())
            ->method('getDefinition')
            ->with($this->equalTo('oro_activity_contact.provider'))
            ->will($this->returnValue($definition));
        $this->container->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with($this->equalTo('oro_activity_direction.provider'))
            ->will($this->returnValue($serviceIds));

        $compilerPass = new DirectionProviderPass();
        $compilerPass->process($this->container);
    }

    public function testProcessEmptyProviders()
    {
        $this->container->expects($this->once())
            ->method('hasDefinition')
            ->with($this->equalTo('oro_activity_contact.provider'))
            ->will($this->returnValue(true));

        $this->container->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with($this->equalTo('oro_activity_direction.provider'))
            ->will($this->returnValue([]));

        $this->container->expects($this->never())
            ->method('getDefinition')
            ->with($this->equalTo('oro_activity_contact.provider'));

        $compilerPass = new DirectionProviderPass();
        $compilerPass->process($this->container);
    }
}
