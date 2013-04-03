<?php

namespace Oro\Bundle\GridBundle\Property;

class FixedProperty extends AbstractProperty
{
    /**
     * @var string
     */
    protected $valueKey;

    /**
     * @param string $name
     * @param string $valueKey
     */
    public function __construct($name, $valueKey = null)
    {
        $this->name = $name;
        $this->valueKey = $valueKey ? $valueKey : $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($data)
    {
        return $this->getDataValue($data, $this->valueKey);
    }
}
