<?php

namespace Oro\Bundle\GridBundle\Property;

use Oro\Bundle\GridBundle\Common\Collection;

class PropertyCollection extends Collection
{
    /**
     * @param PropertyInterface $property
     * @return void
     * @throws \InvalidArgumentException
     */
    public function add($property)
    {
        if (!$property instanceof PropertyInterface) {
            throw new \InvalidArgumentException(
                '$property should be instance of use Oro\Bundle\GridBundle\Property\PropertyInterface'
            );
        }
        parent::add($property);
    }
}
