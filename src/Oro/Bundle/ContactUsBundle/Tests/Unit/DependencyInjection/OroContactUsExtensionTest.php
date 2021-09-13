<?php

namespace Oro\Bundle\ContactUsBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\ContactUsBundle\Controller\Api\Rest\ContactRequestController;
use Oro\Bundle\ContactUsBundle\DependencyInjection\OroContactUsExtension;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;

class OroContactUsExtensionTest extends ExtensionTestCase
{
    public function testLoad(): void
    {
        $this->loadExtension(new OroContactUsExtension());

        $expectedDefinitions = [
            ContactRequestController::class,
        ];

        $this->assertDefinitionsLoaded($expectedDefinitions);
    }
}
