<?php

namespace Oro\Bundle\CaseBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Oro\Bundle\CaseBundle\DependencyInjection\OroCaseExtension;

class OroCaseExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OroCaseExtension
     */
    private $extension;

    /**
     * @var ContainerBuilder
     */
    private $container;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->extension = new OroCaseExtension();
    }

    public function testLoad()
    {
        $this->extension->load(array(), $this->container);
    }
}
