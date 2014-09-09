<?php

namespace Oro\Bundle\UIBundle\Tests\Unit\DependencyInjection\Compiler;

use OroCRM\Bundle\CampaignBundle\DependencyInjection\Compiler\TransportPass;
use Symfony\Component\DependencyInjection\Reference;

class TransportPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $definition = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();
        $containerBuilder = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $containerBuilder->expects($this->once())
            ->method('hasDefinition')
            ->with(TransportPass::SERVICE)
            ->will($this->returnValue(true));
        $containerBuilder->expects($this->once())
            ->method('getDefinition')
            ->with(TransportPass::SERVICE)
            ->will($this->returnValue($definition));

        $services = array('testId' => array());
        $containerBuilder->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with(TransportPass::TAG)
            ->will($this->returnValue($services));
        $definition->expects($this->once())
            ->method('addMethodCall')
            ->with('addTransport', array(new Reference('testId')));

        $pass = new TransportPass();
        $pass->process($containerBuilder);
    }
}
