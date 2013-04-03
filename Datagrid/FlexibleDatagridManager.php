<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

abstract class FlexibleDatagridManager extends DatagridManager
{
    /**
     * @var FlexibleManager
     */
    protected $flexibleManager;

    /**
     * @var Attribute[]
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
            'filter' => FilterInterface::TYPE_FLEXIBLE_STRING,
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
        AbstractAttributeType::BACKEND_TYPE_TEXT => array(
            'field'  => FieldDescriptionInterface::TYPE_TEXT,
            'filter' => FilterInterface::TYPE_FLEXIBLE_STRING,
        ),
        AbstractAttributeType::BACKEND_TYPE_VARCHAR => array(
            'field' => FieldDescriptionInterface::TYPE_TEXT,
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
     * @return Attribute[]
     */
    public function getFlexibleAttributes()
    {
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
     * @param $flexibleFieldType
     * @return string
     * @throws \LogicException
     */
    public function convertFlexibleTypeToFieldType($flexibleFieldType)
    {
        if (!isset(self::$typeMatches[$flexibleFieldType]['field'])) {
            throw new \LogicException('Unknown flexible backend field type.');
        }

        return self::$typeMatches[$flexibleFieldType]['field'];
    }

    /**
     * @param $flexibleFieldType
     * @return string
     * @throws \LogicException
     */
    public function convertFlexibleTypeToFilterType($flexibleFieldType)
    {
        if (!isset(self::$typeMatches[$flexibleFieldType]['filter'])) {
            throw new \LogicException('Unknown flexible backend filter type.');
        }

        return self::$typeMatches[$flexibleFieldType]['filter'];
    }
}
