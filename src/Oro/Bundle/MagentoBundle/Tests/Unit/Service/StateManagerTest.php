<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Service;

use Oro\Bundle\MagentoBundle\Service\StateManager;

class StateManagerTest extends \PHPUnit\Framework\TestCase
{
    const STATE_REQUIRE_INFO = 1;
    const STATE_REQUIRE_ADDRESS = 2;

    public function testStates()
    {
        $object = new \stdClass();
        $object->stateField = null;

        $stateManager = new StateManager();
        $this->assertFalse($stateManager->isInState($object->stateField, self::STATE_REQUIRE_ADDRESS));
        $this->assertFalse($stateManager->isInState($object->stateField, self::STATE_REQUIRE_INFO));

        $stateManager->addState($object, 'stateField', self::STATE_REQUIRE_ADDRESS);
        $this->assertTrue($stateManager->isInState($object->stateField, self::STATE_REQUIRE_ADDRESS));
        $this->assertFalse($stateManager->isInState($object->stateField, self::STATE_REQUIRE_INFO));

        $stateManager->addState($object, 'stateField', self::STATE_REQUIRE_INFO);
        $this->assertTrue($stateManager->isInState($object->stateField, self::STATE_REQUIRE_ADDRESS));
        $this->assertTrue($stateManager->isInState($object->stateField, self::STATE_REQUIRE_INFO));

        $stateManager->removeState($object, 'stateField', self::STATE_REQUIRE_ADDRESS);
        $this->assertFalse($stateManager->isInState($object->stateField, self::STATE_REQUIRE_ADDRESS));
        $this->assertTrue($stateManager->isInState($object->stateField, self::STATE_REQUIRE_INFO));

        $stateManager->removeState($object, 'stateField', self::STATE_REQUIRE_INFO);
        $this->assertFalse($stateManager->isInState($object->stateField, self::STATE_REQUIRE_ADDRESS));
        $this->assertFalse($stateManager->isInState($object->stateField, self::STATE_REQUIRE_INFO));

        $stateManager->addState($object, 'stateField', self::STATE_REQUIRE_ADDRESS | self::STATE_REQUIRE_INFO);
        $this->assertTrue($stateManager->isInState($object->stateField, self::STATE_REQUIRE_ADDRESS));
        $this->assertTrue($stateManager->isInState($object->stateField, self::STATE_REQUIRE_INFO));

        $stateManager->addState($object, 'stateField', self::STATE_REQUIRE_ADDRESS);
        $this->assertTrue($stateManager->isInState($object->stateField, self::STATE_REQUIRE_ADDRESS));
        $this->assertTrue($stateManager->isInState($object->stateField, self::STATE_REQUIRE_INFO));

        $stateManager->removeState($object, 'stateField', self::STATE_REQUIRE_ADDRESS);
        $stateManager->removeState($object, 'stateField', self::STATE_REQUIRE_ADDRESS);
        $this->assertFalse($stateManager->isInState($object->stateField, self::STATE_REQUIRE_ADDRESS));
        $this->assertTrue($stateManager->isInState($object->stateField, self::STATE_REQUIRE_INFO));
    }
}
