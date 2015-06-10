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
            && ($attribute === self::ATTRIBUTE_EDIT
                && (!$this->object->getOriginId() && !$this->object->isGuest())
            )
        ) {
            return self::ACCESS_DENIED;
        }

        if (is_a($this->object, $this->className, true)
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
