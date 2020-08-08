<?php

namespace Oro\Bundle\ContactUsBundle\Model;

use Oro\Bundle\ContactUsBundle\Entity\AbstractContactRequest;

/**
 * Makes ContactRequest entity extendable and adds "virtual" methods.
 *
 * @codingStandardsIgnoreStart
 * @method null|\Oro\Bundle\CustomerBundle\Entity\CustomerUser getCustomerUser() This method is available only in OroCommerce.
 * @method void setCustomerUser(\Oro\Bundle\CustomerBundle\Entity\CustomerUser $customerUser) This method is available only in OroCommerce.
 * @codingStandardsIgnoreEnd
 */
class ExtendContactRequest extends AbstractContactRequest
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
}
