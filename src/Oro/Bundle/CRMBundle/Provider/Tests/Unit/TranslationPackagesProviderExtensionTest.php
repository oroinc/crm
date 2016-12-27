<?php

namespace Oro\Bundle\CRMBundle\Provider\Tests\Unit;

use Oro\Bundle\CRMBundle\Provider\TranslationPackagesProviderExtension;

class TranslationPackagesProviderExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPackageNames()
    {
        $extension = new TranslationPackagesProviderExtension();
        $this->assertEquals([TranslationPackagesProviderExtension::PACKAGE_NAME], $extension->getPackageNames());
    }
}
