<?php

namespace Oro\Bundle\ActivityContactBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\ActivityContactBundle\DependencyInjection\OroActivityContactExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroActivityContactExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');

        $extension = new OroActivityContactExtension();
        $extension->load([], $container);

        self::assertNotEmpty($container->getDefinitions());
    }
}
