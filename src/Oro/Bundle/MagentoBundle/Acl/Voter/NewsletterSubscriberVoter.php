<?php

namespace Oro\Bundle\MagentoBundle\Acl\Voter;

use Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber;

class NewsletterSubscriberVoter extends AbstractTwoWaySyncVoter
{
    /**
     * @var NewsletterSubscriber
     */
    protected $object;

    /**
     * {@inheritdoc}
     */
    protected function getPermissionForAttribute($class, $identifier, $attribute)
    {
        if (is_a($this->object, $this->className, true)
            && ($this->object->getCustomer() && !$this->object->getCustomer()->getOriginId())
        ) {
            return self::ACCESS_DENIED;
        }

        if (is_a($this->object, $this->className, true)
            && $this->object->getChannel()
            && !$this->settingsProvider->isChannelApplicable($this->object->getChannel()->getId())
        ) {
            return self::ACCESS_DENIED;
        }

        if (!$this->settingsProvider->hasApplicableChannels()) {
            return self::ACCESS_DENIED;
        }

        return self::ACCESS_ABSTAIN;
    }
}
