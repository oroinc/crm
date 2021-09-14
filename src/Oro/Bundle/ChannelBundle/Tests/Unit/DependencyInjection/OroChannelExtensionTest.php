<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\ChannelBundle\Controller\Api\Rest\ChannelController;
use Oro\Bundle\ChannelBundle\Controller\Api\Rest\CustomerSearchController;
use Oro\Bundle\ChannelBundle\DependencyInjection\OroChannelExtension;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;

class OroChannelExtensionTest extends ExtensionTestCase
{
    public function testLoad(): void
    {
        $this->loadExtension(new OroChannelExtension());

        $expectedDefinitions = [
            ChannelController::class,
            CustomerSearchController::class,
        ];

        $this->assertDefinitionsLoaded($expectedDefinitions);
    }
}
