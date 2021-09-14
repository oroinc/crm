<?php

namespace Oro\Bundle\CaseBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\CaseBundle\Controller\Api\Rest\CaseController;
use Oro\Bundle\CaseBundle\Controller\Api\Rest\CommentController;
use Oro\Bundle\CaseBundle\DependencyInjection\OroCaseExtension;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;

class OroCaseExtensionTest extends ExtensionTestCase
{
    public function testLoad(): void
    {
        $this->loadExtension(new OroCaseExtension());

        $expectedDefinitions = [
            CaseController::class,
            CommentController::class,
        ];

        $this->assertDefinitionsLoaded($expectedDefinitions);
    }
}
