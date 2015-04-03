<?php

namespace OroCRM\Bundle\MagentoBundle\Acl\Voter;

use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class CustomerVoter extends AbstractTwoWaySyncVoter
{
    /**
     * @var Customer|ObjectIdentityInterface
     */
    protected $object;

    /**
     * {@inheritdoc}
     */
    protected function getPermissionForAttribute($class, $identifier, $attribute)
    {
        if (is_a($this->object, $this->className, true)) {
            if (!$this->settingsProvider->isChannelApplicable($this->object->getChannel()->getId(), false)) {
                return self::ACCESS_DENIED;
            }

            if ($attribute === self::ATTRIBUTE_EDIT && !$this->object->getOriginId()) {
                return self::ACCESS_DENIED;
            }
        }

        return parent::getPermissionForAttribute($class, $identifier, $attribute);
    }
}
