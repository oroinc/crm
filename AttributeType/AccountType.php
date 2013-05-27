<?php
namespace OroCRM\Bundle\AccountBundle\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

class AccountType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_account_attribute_account';
    }
}
