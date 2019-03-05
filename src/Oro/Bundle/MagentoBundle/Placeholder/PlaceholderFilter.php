<?php

namespace Oro\Bundle\MagentoBundle\Placeholder;

use Doctrine\Common\Util\ClassUtils;

/**
 * Helper class that can be used in placeholder configuration files
 * to check is the entity an instance of Oro\Bundle\MagentoBundle\Entity\Customer
 */
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
        return $entity ? ClassUtils::getClass($entity) === 'Oro\Bundle\MagentoBundle\Entity\Customer' : false;
    }

    /**
     * Checks if the array contains at least one magento customer
     *
     * @param array $entities
     * @return bool
     */
    public function containsApplicable(array $entities)
    {
        foreach ($entities as $entity) {
            if ($this->isApplicable($entity)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $entities
     * @param bool $byChannel
     *
     * @return bool
     */
    public function isEventsChartApplicable(array $entities, $byChannel)
    {
        if ($byChannel) {
            return false;
        }

        return $this->containsApplicable($entities);
    }

    /**
     * @param array $entities
     * @param bool $byChannel
     *
     * @return bool
     */
    public function isChannelChartApplicable(array $entities, $byChannel)
    {
        if (!$byChannel) {
            return false;
        }

        return $this->containsApplicable($entities);
    }
}
