<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\ContactBundle\DependencyInjection\Compiler\EmailHolderHelperConfigPass;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EmailHolderHelperConfigPassTest extends \PHPUnit\Framework\TestCase
{
    /** @var EmailHolderHelperConfigPass */
    private $compiler;

    protected function setUp(): void
    {
        $this->compiler = new EmailHolderHelperConfigPass();
    }

    public function testProcessWhenNoEmailHolderHelper()
    {
        $container = new ContainerBuilder();

        $this->compiler->process($container);
    }

    public function testProcess()
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
