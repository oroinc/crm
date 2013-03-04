<?php

namespace Oro\Bundle\UserBundle\Tests\Functional\Acl;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Oro\Bundle\UserBundle\Acl\AclPointcut;
use Oro\Bundle\UserBundle\Tests\Unit\Fixture\Controller\MainTestController;

class AclPointcutTest extends WebTestCase
{
    public function testMatchesMethod()
    {
        $this->getKernelClass();
        $kernel = new \AppKernel("dev", true);
        $kernel->boot();
        $this->application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
        $this->container = $kernel->getContainer();
        $this->application->setAutoExit(false);

        $pointcut = new AclPointcut($this->container->get('annotation_reader'));

        $testControllerReflection = new \ReflectionClass(new MainTestController());

        $this->assertEquals(true, $pointcut->matchesMethod($testControllerReflection->getMethod('test1Action')));
        $this->assertEquals(false, $pointcut->matchesMethod($testControllerReflection->getMethod('testNoAclAction')));
    }
}
