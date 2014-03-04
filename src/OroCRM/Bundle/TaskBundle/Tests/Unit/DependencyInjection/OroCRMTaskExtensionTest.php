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

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->extension = new OroCRMTaskExtension();
    }

    public function testLoad()
    {
        $this->extension->load(array(), $this->container);
    }
}
