<?php

namespace Oro\Bundle\MagentoBundle\Placeholder;

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
        return ClassUtils::getClass($entity) === 'Oro\Bundle\MagentoBundle\Entity\Customer';
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
