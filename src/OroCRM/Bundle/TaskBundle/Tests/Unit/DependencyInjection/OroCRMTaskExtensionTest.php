<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use OroCRM\Bundle\TaskBundle\DependencyInjection\OroCRMTaskExtension;

class OroCRMTaskExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OroCRMTaskExtension
     */
    private $extension;

    /**
     * @var ContainerBuilder
     */
    private $container;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->extension = new OroCRMTaskExtension();
    }

    public function testLoad()
    {
        $this->extension->load([], $this->container);
        $this->assertTrue($this->container->getParameter('orocrm_task.calendar_provider.my_tasks.enabled'));
    }

    public function testLoadWithConfigs()
    {
        $this->extension->load(
            [
                ['my_tasks_in_calendar' => false]
            ],
            $this->container
        );
        $this->assertFalse($this->container->getParameter('orocrm_task.calendar_provider.my_tasks.enabled'));
    }
}
