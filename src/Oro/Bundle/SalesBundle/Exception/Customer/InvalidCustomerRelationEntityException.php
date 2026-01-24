<?php

namespace Oro\Bundle\SalesBundle\Exception\Customer;

use Oro\Bundle\EntityExtendBundle\Exception\InvalidRelationEntityException;

/**
 * Thrown when attempting to establish a customer relation with an invalid entity type
 * that does not support customer associations.
 */
class InvalidCustomerRelationEntityException extends InvalidRelationEntityException
{
}
