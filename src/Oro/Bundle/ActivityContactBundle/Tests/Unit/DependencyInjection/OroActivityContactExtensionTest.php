<?php

namespace Oro\Bundle\ActivityContactBundle\Bundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\ActivityContactBundle\DependencyInjection\OroActivityContactExtension;

class OroActivityContactExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $configuration = new ContainerBuilder();
        $loader        = new OroActivityContactExtension();
        $loader->load([], $configuration);
        $this->assertTrue($configuration instanceof ContainerBuilder);
    }
}
