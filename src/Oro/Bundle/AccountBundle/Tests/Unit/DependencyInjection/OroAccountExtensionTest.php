<?php

namespace Oro\Bundle\AccountBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\AccountBundle\DependencyInjection\OroAccountExtension;
use Oro\Bundle\AccountBundle\OroAccountBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroAccountExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');

        $extension = new OroAccountExtension();
        $extension->load([], $container);

        self::assertNotEmpty($container->getDefinitions());
    }

    public function testPrependForProdEnvironment(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');

        $extension = new OroAccountExtension();
        $extension->prepend($container);

        self::assertEquals([], $container->getExtensionConfig('twig'));
    }

    public function testPrependForTestEnvironment(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');

        $extension = new OroAccountExtension();
        $extension->prepend($container);

        self::assertEquals(
            [
                ['paths' => [(new OroAccountBundle())->getPath() . '/Tests/Functional/Stub/views']]
            ],
            $container->getExtensionConfig('twig')
        );
    }
}
