<?php

namespace OroCRM\Bundle\ActivityContactBundle\Bundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use OroCRM\Bundle\ActivityContactBundle\DependencyInjection\OroCRMActivityContactExtension;

class OroCRMActivityContactExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $configuration = new ContainerBuilder();
        $loader        = new OroCRMActivityContactExtension();
        $loader->load([], $configuration);
        $this->assertTrue($configuration instanceof ContainerBuilder);
    }
}
