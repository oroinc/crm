<?php

namespace Oro\Bundle\MagentoBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Email;

class EmailAddressListConstraint extends Email
{
    /**
     * @var string
     */
    public $message = 'oro.magento.invalid_email_address.message';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'oro_magento.validator.email_address_list';
    }
}
