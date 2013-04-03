<?php

namespace Oro\Bundle\GridBundle\Property;

use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

class FieldProperty extends AbstractProperty
{
    /**
     * @var FieldDescriptionInterface
     */
    protected $field;

    /**
     * @param FieldDescriptionInterface $field
     */
    public function __construct(FieldDescriptionInterface $field)
    {
        $this->field = $field;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->field->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($data)
    {
        return $this->format($this->getRawValue($data));
    }

    /**
     * Get raw value from object
     *
     * @param mixed $data
     * @return mixed|null
     * @throws \LogicException
     */
    protected function getRawValue($data)
    {
        $fieldName = $this->field->getName();
        if (is_array($data)) {
            return isset($data[$fieldName]) ? $data[$fieldName] : null;
        }

        $codeOption = $this->field->getOption('code');
        if ($codeOption && method_exists($data, $codeOption)) {
            return call_user_func(array($data, $codeOption));
        }

        return $this->getDataValue($data, $this->field->getFieldName());
    }

    /**
     * Format raw value.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function format($value)
    {
        if (null === $value) {
            return $value;
        }

        switch ($this->getFieldType()) {
            case FieldDescriptionInterface::TYPE_DATETIME:
            case FieldDescriptionInterface::TYPE_DATE:
                if ($value instanceof \DateTime) {
                    $value = $value->format(\DateTime::ISO8601);
                }
                $result = (string)$value;
                break;
            case FieldDescriptionInterface::TYPE_TEXT:
                $result = (string)$value;
                break;
            case FieldDescriptionInterface::TYPE_DECIMAL:
                $result = floatval($value);
                break;
            case FieldDescriptionInterface::TYPE_INTEGER:
                $result = intval($value);
                break;
            default:
                $result = $value;
        }

        if (is_object($result) && is_callable(array($result, '__toString'))) {
            $result = (string)$result;
        }

        return $result;
    }

    protected function getFieldType()
    {
        return $this->field->getType() ? : $this->field->getOption('type');
    }
}
