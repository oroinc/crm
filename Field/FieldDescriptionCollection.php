<?php

namespace Oro\Bundle\GridBundle\Field;

use Oro\Bundle\GridBundle\Common\Collection;

class FieldDescriptionCollection extends Collection
{
    /**
     * @param FieldDescriptionInterface $fieldDescription
     * @return void
     * @throws \InvalidArgumentException
     */
    public function add($fieldDescription)
    {
        if (!$fieldDescription instanceof FieldDescriptionInterface) {
            throw new \InvalidArgumentException(
                '$fieldDescription should be instance of use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface'
            );
        }
        parent::add($fieldDescription);
    }
}
