<?php

namespace Oro\Bundle\SalesBundle\Model;

/**
 * The model to make Customer entity extendable.
 * The real implementation of this class is auto generated.
 */
class ExtendCustomer
{
    /**
     * Constructor
     *
     * The real implementation of this method is auto generated.
     *
     * IMPORTANT: If the derived class has own constructor it must call parent constructor.
     */
    public function __construct()
    {
    }

    /**
     * Checks if the given entity type is a supported customer.
     *
     * @param string $targetClass
     *
     * @return bool
     */
    public function supportCustomerTarget($targetClass)
    {
        return false;
    }

    /**
     * Gets a customer entity that is associated with an account.
     *
     * @return object|null
     */
    public function getCustomerTarget()
    {
        return null;
    }

    /**
     * Sets a customer entity that should be associated with an account.
     *
     * @param object $target
     *
     * @return self
     */
    public function setCustomerTarget($target)
    {
        return $this;
    }
}
