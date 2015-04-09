<?php

namespace OroCRM\Bundle\MagentoBundle\Acl\Voter;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class CustomerVoter extends AbstractTwoWaySyncVoter
{
    /**
     * @var Customer
     */
    protected $object;

    /**
     * {@inheritdoc}
     */
    protected function getPermissionForAttribute($class, $identifier, $attribute)
    {
        if (is_a($this->object, $this->className, true)
            && ($attribute === self::ATTRIBUTE_EDIT && !$this->object->getOriginId())
        ) {
            return self::ACCESS_DENIED;
        }

        return parent::getPermissionForAttribute($class, $identifier, $attribute);
    }
}
