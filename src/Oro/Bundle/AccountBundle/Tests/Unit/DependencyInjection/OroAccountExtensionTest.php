<?php

namespace Oro\Bundle\AccountBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\AccountBundle\Controller\Api\Rest\AccountController;
use Oro\Bundle\AccountBundle\DependencyInjection\OroAccountExtension;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;

class OroAccountExtensionTest extends ExtensionTestCase
{
    public function testLoad(): void
    {
        $this->loadExtension(new OroAccountExtension());

        $expectedDefinitions = [
            'oro_account.importexport.configuration_provider.account',
            AccountController::class,
        ];
        $this->assertDefinitionsLoaded($expectedDefinitions);
    }
}
