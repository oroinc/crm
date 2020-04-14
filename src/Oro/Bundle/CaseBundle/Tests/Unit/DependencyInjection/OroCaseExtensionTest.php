<?php

namespace Oro\Bundle\CaseBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\CaseBundle\DependencyInjection\OroCaseExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroCaseExtensionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OroCaseExtension
     */
    private $extension;

    /**
     * @var ContainerBuilder
     */
    private $container;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->extension = new OroCaseExtension();
    }

    public function testLoad()
    {
        $this->extension->load(array(), $this->container);
    }
}
