<?php

namespace Oro\Bundle\GridBundle\Property;

abstract class AbstractProperty implements PropertyInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get data from value by key
     *
     * @param mixed $data
     * @param string $key
     * @return mixed
     * @throws \LogicException
     */
    protected function getDataValue($data, $key)
    {
        if (is_array($data)) {
            return isset($data[$key]) ? $data[$key] : null;
        }

        $fieldName = $key;
        $camelizedFieldName = self::camelize($fieldName);
        $getters = array();
        $getters[] = 'get' . $camelizedFieldName;
        $getters[] = 'is' . $camelizedFieldName;

        foreach ($getters as $getter) {
            if (method_exists($data, $getter)) {
                return call_user_func(array($data, $getter));
            }
        }

        if (isset($data->{$fieldName})) {
            return $data->{$fieldName};
        }

        throw new \LogicException(sprintf('Unable to retrieve the value of "%s" property', $this->getName()));
    }

    /**
     * Camelize a string
     *
     * @static
     * @param string $property
     * @return string
     */
    private static function camelize($property)
    {
        return preg_replace(
            array('/(^|_| )+(.)/e', '/\.(.)/e'),
            array("strtoupper('\\2')", "'_'.strtoupper('\\1')"),
            $property
        );
    }
}
