<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\ContactBundle\DependencyInjection\Compiler\EmailHolderHelperConfigPass;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class EmailHolderHelperConfigPassTest extends \PHPUnit\Framework\TestCase
{
    public function testNoTargetService()
    {
        $container = $this->createMock(ContainerBuilder::class);

        $container->expects($this->once())
            ->method('hasDefinition')
            ->with('oro_email.email_holder_helper')
            ->will($this->returnValue(false));
        $container->expects($this->never())
            ->method('getDefinition');

        $compiler = new EmailHolderHelperConfigPass();
        $compiler->process($container);
    }

    public function testProcess()
    {
        $container = $this->createMock(ContainerBuilder::class);

        $serviceDef = $this->createMock(Definition::class);

        $container->expects($this->once())
            ->method('hasDefinition')
            ->with('oro_email.email_holder_helper')
            ->will($this->returnValue(true));
        $container->expects($this->once())
            ->method('getDefinition')
            ->with('oro_email.email_holder_helper')
            ->will($this->returnValue($serviceDef));

        $serviceDef->expects($this->once())
            ->method('addMethodCall')
            ->with('addTargetEntity', [Contact::class]);

        $compiler = new EmailHolderHelperConfigPass();
        $compiler->process($container);
    }
}
