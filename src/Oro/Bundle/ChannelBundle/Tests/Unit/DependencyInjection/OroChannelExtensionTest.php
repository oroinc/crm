<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\ChannelBundle\DependencyInjection\OroChannelExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroChannelExtensionTest extends \PHPUnit\Framework\TestCase
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
