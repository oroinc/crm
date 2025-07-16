<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\ContactBundle\DependencyInjection\Compiler\EmailHolderHelperConfigPass;
use Oro\Bundle\ContactBundle\Entity\Contact;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EmailHolderHelperConfigPassTest extends TestCase
{
    private EmailHolderHelperConfigPass $compiler;

    #[\Override]
    protected function setUp(): void
    {
        $this->compiler = new EmailHolderHelperConfigPass();
    }

    public function testProcessWhenNoEmailHolderHelper(): void
    {
        $container = new ContainerBuilder();

        $this->compiler->process($container);
    }

    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $emailHolderHelperDef = $container->register('oro_email.email_holder_helper');

        $this->compiler->process($container);

        self::assertEquals(
            [
                ['addTargetEntity', [Contact::class]]
            ],
            $emailHolderHelperDef->getMethodCalls()
        );
    }
}
