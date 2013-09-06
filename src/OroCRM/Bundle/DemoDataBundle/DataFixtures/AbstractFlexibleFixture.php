<?php
namespace OroCRM\Bundle\DemoDataBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexible;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeOption;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

abstract class AbstractFlexibleFixture extends AbstractFixture
{
    /**
     * Sets a flexible attribute value
     *
     * @param EntityManager $entityManger
     * @param AbstractFlexible $flexibleEntity
     * @param string $attributeCode
     * @param string $value
     * @return void
     * @throws \LogicException
     */
    protected function setFlexibleAttributeValue(
        $entityManger,
        AbstractFlexible $flexibleEntity,
        $attributeCode,
        $value
    ) {
        if ($attribute = $this->findAttribute($entityManger, $attributeCode)) {
            $this->getFlexibleValueForAttribute($entityManger, $flexibleEntity, $attribute)->setData($value);
        } else {
            throw new \LogicException(sprintf('Cannot set value, attribute "%s" is missing', $attributeCode));
        }
    }

    /**
     * Sets a flexible attribute value as option with given value
     *
     * @param EntityManager $entityManger
     * @param AbstractFlexible $flexibleEntity
     * @param string $attributeCode
     * @param string $value
     * @return void
     * @throws \LogicException
     */
    protected function setFlexibleAttributeValueOption(
        $entityManger,
        AbstractFlexible $flexibleEntity,
        $attributeCode,
        $value
    ) {
        if ($attribute = $this->findAttribute($entityManger, $attributeCode)) {
            $option = $this->findAttributeOptionWithValue($entityManger, $attribute, $value);
            $this->getFlexibleValueForAttribute($entityManger, $flexibleEntity, $attribute)->setOption($option);
        } else {
            throw new \LogicException(sprintf('Cannot set value, attribute "%s" is missing', $attributeCode));
        }
    }
    /**
     * Adds option values to flexible attribute value
     *
     * @param EntityManager $entityManger
     * @param AbstractFlexible $flexibleEntity
     * @param string $attributeCode
     * @param array $values
     * @return void
     * @throws \LogicException
     */
    protected function addFlexibleAttributeValueOptions(
        $entityManger,
        AbstractFlexible $flexibleEntity,
        $attributeCode,
        array $values
    ) {
        if ($attribute = $this->findAttribute($entityManger, $attributeCode)) {
            $flexibleValue = $this->getFlexibleValueForAttribute($entityManger, $flexibleEntity, $attribute);
            foreach ($values as $value) {
                $option = $this->findAttributeOptionWithValue($entityManger, $attribute, $value);
                $flexibleValue->addOption($option);
            }
        } else {
            throw new \LogicException(sprintf('Cannot set value, attribute "%s" is missing', $attributeCode));
        }
    }

    /**
     * Finds an attribute option with value
     *
     * @param EntityManager $entityManger
     * @param AbstractAttribute $attribute
     * @param string $value
     * @return AbstractAttributeOption
     * @throws \LogicException
     */
    protected function findAttributeOptionWithValue($entityManger, AbstractAttribute $attribute, $value)
    {
        /** @var $options \Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption[] */
        $options = $entityManger->getAttributeOptionRepository()->findBy(
            array('attribute' => $attribute)
        );

        foreach ($options as $option) {
            if ($value == $option->getOptionValue()->getValue()) {
                return $option;
            }
        }

        throw new \LogicException(sprintf('Cannot find attribute option with value "%s"', $value));
    }

    /**
     * Gets or creates a flexible value for attribute
     *
     * @param EntityManager $entityManger
     * @param AbstractFlexible $flexibleEntity
     * @param AbstractAttribute $attribute
     * @return FlexibleValueInterface
     */
    protected function getFlexibleValueForAttribute(
        $entityManger,
        AbstractFlexible $flexibleEntity,
        AbstractAttribute $attribute
    ) {
        $flexibleValue = $flexibleEntity->getValue($attribute->getCode());
        if (!$flexibleValue) {
            $flexibleValue = $entityManger->createFlexibleValue();
            $flexibleValue->setAttribute($attribute);
            $flexibleEntity->addValue($flexibleValue);
        }
        return $flexibleValue;
    }

    /**
     * Finds an attribute
     *
     * @param EntityManager $entityManger
     * @param string $attributeCode
     * @return AbstractAttribute
     */
    protected function findAttribute($entityManger, $attributeCode)
    {
        return $entityManger->getFlexibleRepository()->findAttributeByCode($attributeCode);
    }

    /**
     * Create an attribute
     *
     * @param EntityManager $entityManger
     * @param string $attributeType
     * @param string $attributeCode
     * @return AbstractAttribute
     */
    protected function createAttribute($entityManger, $attributeType, $attributeCode)
    {
        $result = $entityManger->createAttribute($attributeType);
        $result->setCode($attributeCode);
        $result->setLabel($attributeCode);

        return $result;
    }
}
