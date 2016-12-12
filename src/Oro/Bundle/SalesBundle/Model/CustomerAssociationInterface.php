<?php

namespace Oro\Bundle\SalesBundle\Model;

interface CustomerAssociationInterface
{
    /**
     * Checks if this entity can be associated with the given target entity type
     *
     * @param string $targetClass The class name of the target entity
     *
     * @return bool
     */
    public function supportCustomerTarget($targetClass);

    /**
     * Sets the entity this entity is associated with
     *
     * @param object $target Any configurable entity that can be associated with this type of entity
     *
     * @return object This object
     */
    public function setCustomerTarget($target);

    /**
     * Returns array with all associated entities
     *
     * @return array
     */
    public function getCustomerTargetEntities();

    /**
     * Gets the entity this entity is associated with
     *
     * @return object|null Any configurable entity
     */
    public function getCustomerTarget();
}
