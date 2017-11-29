<?php

namespace Oro\Bundle\AccountBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\AccountBundle\DependencyInjection\OroAccountExtension;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;

class OroAccountExtensionTest extends ExtensionTestCase
{
    public function testLoad()
    {
        $this->loadExtension(new OroAccountExtension());

        $expectedDefinitions = [
            'oro_account.importexport.configuration_provider.account',
        ];
        $this->assertDefinitionsLoaded($expectedDefinitions);
    }

    public function testGetAlias()
    {
        $extension = new OroAccountExtension();

        $this->assertEquals('oro_account', $extension->getAlias());
    }
}
