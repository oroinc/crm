<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\ContactBundle\DependencyInjection\OroContactExtension;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;

class OroContactExtensionTest extends ExtensionTestCase
{
    public function testExtension()
    {
        $extension = new OroContactExtension();

        $this->loadExtension($extension);

        $expectedDefinitions = [
            'oro_contact.importexport.configuration_provider.contact',
        ];
        $this->assertDefinitionsLoaded($expectedDefinitions);
    }
}
