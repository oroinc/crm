<?php

namespace Oro\Bundle\GridBundle\Property;

interface PropertyInterface
{
    /**
     * Get value name
     *
     * @return string
     */
    public function getName();

    /**
     * Get field value from data
     *
     * @param mixed $data
     * @return mixed
     */
    public function getValue($data);
}
