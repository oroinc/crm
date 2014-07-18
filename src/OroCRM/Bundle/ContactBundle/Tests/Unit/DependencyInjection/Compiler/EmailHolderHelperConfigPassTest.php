<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\DependencyInjection\Compiler;

use OroCRM\Bundle\ContactBundle\DependencyInjection\Compiler\EmailHolderHelperConfigPass;

class EmailHolderHelperConfigPassTest extends \PHPUnit_Framework_TestCase
{
    public function testNoTargetService()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $container->expects($this->once())
            ->method('hasDefinition')
            ->with(EmailHolderHelperConfigPass::SERVICE_KEY)
            ->will($this->returnValue(false));
        $container->expects($this->never())
            ->method('getDefinition');

        $compiler = new EmailHolderHelperConfigPass();
        $compiler->process($container);
    }

    public function testProcess()
    {
        $entityClass = 'Test\Entity';

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $serviceDef = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();

        $container->expects($this->once())
            ->method('hasDefinition')
            ->with(EmailHolderHelperConfigPass::SERVICE_KEY)
            ->will($this->returnValue(true));
        $container->expects($this->once())
            ->method('getDefinition')
            ->with(EmailHolderHelperConfigPass::SERVICE_KEY)
            ->will($this->returnValue($serviceDef));
        $container->expects($this->once())
            ->method('getParameter')
            ->with('orocrm_contact.entity.class')
            ->will($this->returnValue($entityClass));

        $serviceDef->expects($this->once())
            ->method('addMethodCall')
            ->with('addTargetEntity', [$entityClass]);

        $compiler = new EmailHolderHelperConfigPass();
        $compiler->process($container);
    }
}
