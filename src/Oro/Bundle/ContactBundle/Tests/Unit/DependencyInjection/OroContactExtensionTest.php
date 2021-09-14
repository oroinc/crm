<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\ContactBundle\Controller\Api\Rest\ContactAddressController;
use Oro\Bundle\ContactBundle\Controller\Api\Rest\ContactController;
use Oro\Bundle\ContactBundle\Controller\Api\Rest\ContactEmailController;
use Oro\Bundle\ContactBundle\Controller\Api\Rest\ContactGroupController;
use Oro\Bundle\ContactBundle\Controller\Api\Rest\ContactPhoneController;
use Oro\Bundle\ContactBundle\DependencyInjection\OroContactExtension;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;

class OroContactExtensionTest extends ExtensionTestCase
{
    public function testExtension(): void
    {
        $extension = new OroContactExtension();

        $this->loadExtension($extension);

        $expectedDefinitions = [
            'oro_contact.importexport.configuration_provider.contact',
            ContactAddressController::class,
            ContactController::class,
            ContactEmailController::class,
            ContactGroupController::class,
            ContactPhoneController::class,
        ];
        $this->assertDefinitionsLoaded($expectedDefinitions);
    }
}
