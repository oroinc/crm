<?php

namespace Oro\Bundle\MagentoBundle\Service;

use Symfony\Component\PropertyAccess\PropertyAccess;

class StateManager
{
    /**
     * @param int $currentState
     * @param int $requiredState
     * @return bool
     */
    public function isInState($currentState, $requiredState)
    {
        return ($currentState & $requiredState) === $requiredState;
    }

    /**
     * @param object $object
     * @param string $field
     * @param int $state
     */
    public function addState($object, $field, $state)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $currentState = $propertyAccessor->getValue($object, $field);
        if (!$this->isInState($currentState, $state)) {
            $currentState |= $state;
            $propertyAccessor->setValue($object, $field, $currentState);
        }
    }

    /**
     * @param object $object
     * @param string $field
     * @param int $state
     */
    public function removeState($object, $field, $state)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $currentState = $propertyAccessor->getValue($object, $field);
        if ($this->isInState($currentState, $state)) {
            $currentState &= ~$state;
            $propertyAccessor->setValue($object, $field, $currentState);
        }
    }
}
