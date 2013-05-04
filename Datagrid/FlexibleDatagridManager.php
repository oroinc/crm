<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

abstract class FlexibleDatagridManager extends DatagridManager
{
    /**
     * @var FlexibleManager
     */
    protected $flexibleManager;

    /**
     * @var AbstractAttribute[]
     */
    protected $attributes;

    /**
     * @var array
     */
    protected static $typeMatches = array(
        AbstractAttributeType::BACKEND_TYPE_DATE => array(
            'field'  => FieldDescriptionInterface::TYPE_DATE,
            'filter' => FilterInterface::TYPE_FLEXIBLE_DATE,
        ),
        AbstractAttributeType::BACKEND_TYPE_DATETIME => array(
            'field'  => FieldDescriptionInterface::TYPE_DATETIME,
            'filter' => FilterInterface::TYPE_FLEXIBLE_DATETIME,
        ),
        AbstractAttributeType::BACKEND_TYPE_DECIMAL => array(
            'field'  => FieldDescriptionInterface::TYPE_DECIMAL,
            'filter' => FilterInterface::TYPE_FLEXIBLE_NUMBER,
        ),
        AbstractAttributeType::BACKEND_TYPE_INTEGER => array(
            'field'  => FieldDescriptionInterface::TYPE_INTEGER,
            'filter' => FilterInterface::TYPE_FLEXIBLE_NUMBER,
        ),
        AbstractAttributeType::BACKEND_TYPE_OPTION => array(
            'field'  => FieldDescriptionInterface::TYPE_OPTIONS,
            'filter' => FilterInterface::TYPE_FLEXIBLE_OPTIONS,
        ),
        AbstractAttributeType::BACKEND_TYPE_OPTIONS => array(
            'field'  => FieldDescriptionInterface::TYPE_OPTIONS,
            'filter' => FilterInterface::TYPE_FLEXIBLE_OPTIONS,
        ),
        AbstractAttributeType::BACKEND_TYPE_TEXT => array(
            'field'  => FieldDescriptionInterface::TYPE_TEXT,
            'filter' => FilterInterface::TYPE_FLEXIBLE_STRING,
        ),
        AbstractAttributeType::BACKEND_TYPE_VARCHAR => array(
            'field' => FieldDescriptionInterface::TYPE_TEXT,
            'filter' => FilterInterface::TYPE_FLEXIBLE_STRING,
        ),
        AbstractAttributeType::BACKEND_TYPE_PRICE => array(
            'field'  => FieldDescriptionInterface::TYPE_TEXT,
            'filter' => FilterInterface::TYPE_FLEXIBLE_STRING,
        ),
        AbstractAttributeType::BACKEND_TYPE_METRIC => array(
            'field'  => FieldDescriptionInterface::TYPE_TEXT,
            'filter' => FilterInterface::TYPE_FLEXIBLE_STRING,
        ),
    );

    /**
     * @param FlexibleManager $flexibleManager
     */
    public function setFlexibleManager(FlexibleManager $flexibleManager)
    {
        $this->flexibleManager = $flexibleManager;

        // TODO: somehow get locale and scope from parameters interface
        $this->flexibleManager->setLocale('en_US');
        $this->flexibleManager->setScope('ecommerce');
    }

    /**
     * Traverse all flexible attributes and add them as fields to collection
     *
     * @param FieldDescriptionCollection $fieldsCollection
     * @param array $options
     */
    protected function configureFlexibleFields(FieldDescriptionCollection $fieldsCollection, array $options = array())
    {
        foreach ($this->getFlexibleAttributes() as $attribute) {
            $attributeCode = $attribute->getCode();
            $fieldsCollection->add(
                $this->createFlexibleField(
                    $attribute,
                    isset($options[$attributeCode]) ? $options[$attributeCode] : array()
                )
            );
        }
    }

    /**
     * Create field by flexible attribute
     *
     * @param AbstractAttribute $attribute
     * @return FieldDescriptionInterface
     */
    protected function createFlexibleField(AbstractAttribute $attribute, array $options = array())
    {
        $field = new FieldDescription();
        $field->setName($attribute->getCode());
        $options = array_merge($this->getFlexibleFieldDefaultOptions($attribute), $options);
        $field->setOptions($options);

        return $field;
    }

    /**
     * Get default options for flexible field
     *
     * @param AbstractAttribute $attribute
     * @return array
     */
    protected function getFlexibleFieldDefaultOptions(AbstractAttribute $attribute)
    {
        $backendType   = $attribute->getBackendType();
        $attributeType = $this->convertFlexibleTypeToFieldType($backendType);
        $filterType    = $this->convertFlexibleTypeToFilterType($backendType);

        $result = array(
            'type'          => $attributeType,
            'label'         => ucfirst($attribute->getCode()),
            'field_name'    => $attribute->getCode(),
            'filter_type'   => $filterType,
            'required'      => false,
            'sortable'      => true,
            'filterable'    => true,
            'flexible_name' => $this->flexibleManager->getFlexibleName()
        );

        if ($attributeType == FieldDescriptionInterface::TYPE_OPTIONS) {
            $result['multiple'] = true;
        }

        return $result;
    }

    /**
     * @deprecated Method should not be used outside of the class
     * @return AbstractAttribute[]
     */
    public function getFlexibleAttributes()
    {
        // TODO Make this method protected
        if (null === $this->attributes) {
            /** @var $attributeRepository \Doctrine\Common\Persistence\ObjectRepository */
            $attributeRepository = $this->flexibleManager->getAttributeRepository();
            $this->attributes = $attributeRepository->findBy(
                array('entityType' => $this->flexibleManager->getFlexibleName())
            );
        }

        return $this->attributes;
    }

    /**
     * @deprecated Method should not be used outside of the class
     * @param $flexibleFieldType
     * @return string
     * @throws \LogicException
     */
    public function convertFlexibleTypeToFieldType($flexibleFieldType)
    {
        // TODO Make this method protected
        if (!isset(self::$typeMatches[$flexibleFieldType]['field'])) {
            throw new \LogicException('Unknown flexible backend field type.');
        }

        return self::$typeMatches[$flexibleFieldType]['field'];
    }

    /**
     * @deprecated Method should not be used outside of the class
     * @param $flexibleFieldType
     * @return string
     * @throws \LogicException
     */
    public function convertFlexibleTypeToFilterType($flexibleFieldType)
    {
        // TODO Make this method protected
        if (!isset(self::$typeMatches[$flexibleFieldType]['filter'])) {
            throw new \LogicException('Unknown flexible backend filter type.');
        }

        return self::$typeMatches[$flexibleFieldType]['filter'];
    }
}
