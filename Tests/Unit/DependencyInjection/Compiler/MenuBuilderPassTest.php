<?php
namespace Oro\Bundle\NavigationBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\NavigationBundle\DependencyInjection\Compiler\MenuBuilderChainPass;
use Symfony\Component\DependencyInjection\Reference;

class MenuBuilderPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessSkip()
    {
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->getMock();
        $containerMock->expects($this->once())
            ->method('hasDefinition')
            ->with('oro_menu.builder_chain')
            ->will($this->returnValue(false));
        $containerMock->expects($this->never())
            ->method('getDefinition');
        $containerMock->expects($this->never())
            ->method('findTaggedServiceIds');

        $compilerPass = new MenuBuilderChainPass();
        $compilerPass->process($containerMock);
    }

    public function testProcess()
    {
        $definition = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->getMock();
        $definition->expects($this->exactly(2))
            ->method('addMethodCall');
        $definition->expects($this->at(0))
            ->method('addMethodCall')
            ->with('addBuilder', array(new Reference('service1'), 'test'));
        $definition->expects($this->at(1))
            ->method('addMethodCall')
            ->with('addBuilder', array(new Reference('service2'), 'test'));

        $serviceIds = array(
            'service1' => array(array('alias' => 'test')),
            'service2' => array(array('alias' => 'test'))
        );

        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->getMock();

        $containerMock->expects($this->once())
            ->method('hasDefinition')
            ->with('oro_menu.builder_chain')
            ->will($this->returnValue(true));

        $containerMock->expects($this->once())
            ->method('getDefinition')
            ->with('oro_menu.builder_chain')
            ->will($this->returnValue($definition));

        $containerMock->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('oro_menu.builder')
            ->will($this->returnValue($serviceIds));

        $compilerPass = new MenuBuilderChainPass();
        $compilerPass->process($containerMock);
    }
}
