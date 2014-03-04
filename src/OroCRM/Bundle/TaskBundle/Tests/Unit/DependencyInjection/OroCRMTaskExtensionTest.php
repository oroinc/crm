<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use OroCRM\Bundle\TaskBundle\DependencyInjection\OroTaskExtension;

class OroTaskExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OroTaskExtension
     */
    private $extension;

    /**
     * @var ContainerBuilder
     */
    private $container;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->extension = new OroTaskExtension();
    }

    public function testLoad()
    {
        $this->extension->load(array(), $this->container);
    }
}
