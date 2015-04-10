<?php

namespace OroCRM\Bundle\MagentoBundle\Acl\Voter;

use OroCRM\Bundle\MagentoBundle\Entity\NewsletterSubscriber;

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

        return parent::getPermissionForAttribute($class, $identifier, $attribute);
    }
}
