<?php

namespace Oro\Bundle\GridBundle\Field;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

class FieldDescription implements FieldDescriptionInterface
{
    /**
     * @var string the field name (of the form)
     */
    protected $fieldName;

    /**
     * @var string the field name
     */
    protected $name;

    /**
     * @var array the option collection
     */
    protected $options = array();

    /**
     * @var string|integer the type
     */
    protected $type;

    /**
     * @var string the template name
     */
    protected $template;

    /**
     * @var array the ORM field information
     */
    protected $fieldMapping;

    /**
     * @var string|integer the original mapping type
     */
    protected $mappingType;

    /**
     * @var array the ORM association mapping
     */
    protected $associationMapping;

    /**
     * {@inheritdoc}
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;

        if (!$this->getFieldName()) {
            $this->setFieldName(substr(strrchr('.' . $name, '.'), 1));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getOption($name, $default = null)
    {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        // set the type if provided
        if (isset($options['type'])) {
            $this->setType($options['type']);
            unset($options['type']);
        }

        // remove property value
        if (isset($options['template'])) {
            $this->setTemplate($options['template']);
            unset($options['template']);
        }

        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function setAssociationMapping($associationMapping)
    {
        $this->associationMapping = $associationMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociationMapping()
    {
        return $this->associationMapping;
    }

    /**
     * set the field mapping information
     *
     * @param array $fieldMapping
     *
     * @return void
     */
    public function setFieldMapping($fieldMapping)
    {
        $this->fieldMapping = $fieldMapping;
    }

    /**
     * return the field mapping definition
     *
     * @return array the field mapping definition
     */
    public function getFieldMapping()
    {
        return $this->fieldMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetEntity()
    {
        if ($this->associationMapping) {
            return $this->associationMapping['targetEntity'];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isIdentifier()
    {
        return isset($this->fieldMapping['id']);
    }

    /**
     * {@inheritdoc}
     */
    public function mergeOption($name, array $options = array())
    {
        if (!isset($this->options[$name])) {
            $this->options[$name] = array();
        }

        if (!is_array($this->options[$name])) {
            throw new \RuntimeException(sprintf('The key "%s" does not point to an array value', $name));
        }

        $this->options[$name] = array_merge($this->options[$name], $options);
    }

    /**
     * {@inheritdoc}
     */
    public function mergeOptions(array $options = array())
    {
        $this->setOptions(array_merge_recursive($this->options, $options));
    }

    /**
     * {@inheritdoc}
     */
    public function setMappingType($mappingType)
    {
        $this->mappingType = $mappingType;
    }

    /**
     * {@inheritdoc}
     */
    public function getMappingType()
    {
        return $this->mappingType;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->getOption('label');
    }

    /**
     * {@inheritdoc}
     */
    public function isSortable()
    {
        return $this->getOption('sortable', false);
    }

    /**
     * {@inheritdoc}
     */
    public function isFilterable()
    {
        return $this->getOption('filterable', false);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortFieldMapping()
    {
        return $this->getOption('sort_field_mapping');
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldValue($object)
    {
        if (is_array($object)) {
            $name = $this->getName();
            return isset($object[$name]) ? $object[$name] : null;
        }

        $fieldName = $this->getFieldName();
        $camelizedFieldName = self::camelize($fieldName);

        $getters = array();
        // prefer method name given in the code option
        if ($this->getOption('code')) {
            $getters[] = $this->getOption('code');
        }
        $getters[] = 'get' . $camelizedFieldName;
        $getters[] = 'is' . $camelizedFieldName;

        foreach ($getters as $getter) {
            if (method_exists($object, $getter)) {
                return $this->convertFieldValue(call_user_func(array($object, $getter)));
            }
        }

        if (isset($object->{$fieldName})) {
            return $this->convertFieldValue($object->{$fieldName});
        }

        throw new \LogicException(sprintf('Unable to retrieve the value of "%s"', $this->getName()));
    }

    /**
     * @param $value
     * @return mixed
     */
    protected function convertFieldValue($value)
    {
        if (null === $value) {
            return $value;
        }
        $valueType = $this->getType() ?: $this->getOption('type');
        switch ($valueType) {
            case AbstractAttributeType::BACKEND_TYPE_DECIMAL:
                return floatval($value);
                break;
            case AbstractAttributeType::BACKEND_TYPE_INTEGER:
                return intval($value);
                break;
            default:
                return $value;
        }
    }

    /**
     * Camelize a string
     *
     * @static
     *
     * @param string $property
     *
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
