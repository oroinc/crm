<?php

namespace OroCRM\Bundle\SalesBundle\Model;

use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;

abstract class ExtendB2bCustomer implements EmailOwnerInterface
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

    /** @inheritdoc */
    abstract public function getId();

    /** @inheritdoc */
    abstract public function getClass();

    /**
     * Get names of fields contain email addresses
     *
     * @return string[]|null
     */
    public function getEmailFields()
    {
        return null;
    }

    /** Stub for EmailOwnerInterface */
    public function getFirstName()
    {
        return null;
    }

    /** Stub for EmailOwnerInterface */
    public function getLastName()
    {
        return null;
    }
}
