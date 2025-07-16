<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\ChannelBundle\DependencyInjection\OroChannelExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroChannelExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');

        $extension = new OroChannelExtension();
        $extension->load([], $container);

        self::assertNotEmpty($container->getDefinitions());
    }
}
