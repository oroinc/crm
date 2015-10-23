<?php

namespace OroCRM\Bundle\MagentoBundle\Placeholder;

use Doctrine\Common\Util\ClassUtils;

class PlaceholderFilter
{
    /**
     * Checks if the entity is magento customer
     *
     * @param object|null $entity
     * @return bool
     */
    public function isApplicable($entity = null)
    {
        return ClassUtils::getClass($entity) === 'OroCRM\Bundle\MagentoBundle\Entity\Customer';
    }
}
