<?php

namespace Oro\Bundle\MagentoBundle\Acl\Voter;

use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\SecurityBundle\Acl\BasicPermission;

/**
 * Implements the following logic:
 * * denies editing of non guest Magento customers that does not have an origin
 * * denies an access Magento customers that belong to not applicable channels
 */
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
        $isCustomer = is_a($this->object, $this->className, true);
        if ($isCustomer
            && BasicPermission::EDIT === $attribute
            && !$this->object->getOriginId()
            && !$this->object->isGuest()
        ) {
            return self::ACCESS_DENIED;
        }

        if ($isCustomer
            && $this->object->getChannel()
            && !$this->settingsProvider->isChannelApplicable($this->object->getChannel()->getId(), false)
        ) {
            return self::ACCESS_DENIED;
        }

        if (!$this->settingsProvider->hasApplicableChannels(false)) {
            return self::ACCESS_DENIED;
        }

        return self::ACCESS_ABSTAIN;
    }
}
