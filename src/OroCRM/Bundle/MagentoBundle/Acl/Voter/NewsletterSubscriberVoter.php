<?php

namespace OroCRM\Bundle\MagentoBundle\Acl\Voter;

use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;

use OroCRM\Bundle\MagentoBundle\Entity\NewsletterSubscriber;

class NewsletterSubscriberVoter extends AbstractTwoWaySyncVoter
{
    /**
     * @var NewsletterSubscriber|ObjectIdentityInterface
     */
    protected $object;

    /**
     * {@inheritdoc}
     */
    protected function getPermissionForAttribute($class, $identifier, $attribute)
    {
        if (is_a($this->object, $this->className, true)) {
            if (!$this->settingsProvider->isChannelApplicable($this->object->getChannel()->getId())) {
                return self::ACCESS_DENIED;
            }

            if ($this->object->getCustomer() && !$this->object->getCustomer()->getOriginId()) {
                return self::ACCESS_DENIED;
            }
        }

        return parent::getPermissionForAttribute($class, $identifier, $attribute);
    }
}
