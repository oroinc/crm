<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer;

use Oro\Bundle\UIBundle\Model\Image;

/**
 * Defines the contract for providing customer type icons used in UI representations.
 */
interface CustomerIconProviderInterface
{
    /**
     * @param object $entity customer entity
     *
     * @return Image|null
     */
    public function getIcon($entity);
}
