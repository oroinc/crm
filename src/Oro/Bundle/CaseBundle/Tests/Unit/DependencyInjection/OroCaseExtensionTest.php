<?php

namespace Oro\Bundle\CaseBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\CaseBundle\DependencyInjection\OroCaseExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroCaseExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();

        $extension = new OroCaseExtension();
        $extension->load([], $container);

        self::assertNotEmpty($container->getDefinitions());
    }
}
