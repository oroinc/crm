<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer;

use Oro\Bundle\UIBundle\Model\Image;

interface CustomerIconProviderInterface
{
    /**
     * @param object $entity customer entity
     *
     * @return Image|null
     */
    public function getIcon($entity);
}
