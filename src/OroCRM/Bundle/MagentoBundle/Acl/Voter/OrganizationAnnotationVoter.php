<?php

namespace Oro\Bundle\MagentoBundle\Acl\Voter;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

class OrganizationAnnotationVoter extends AbstractTwoWaySyncVoter
{
    const RESOURCE_CREATE = 'oro_magento_customer_create';

    /**
     * @var array
     */
    protected $supportedAttributes = [self::RESOURCE_CREATE];

    /**
     * @var Organization
     */
    protected $object;

    /**
     * {@inheritdoc}
     */
    protected function getPermissionForAttribute($class, $identifier, $attribute)
    {
        if (is_a($this->object, $this->className, true)) {
            if ($this->settingsProvider->hasOrganizationApplicableChannels($this->object, false)) {
                return self::ACCESS_GRANTED;
            }

            return self::ACCESS_DENIED;
        }

        return self::ACCESS_ABSTAIN;
    }
}
