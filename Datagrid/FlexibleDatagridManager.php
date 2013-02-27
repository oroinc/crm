<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

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
        AbstractAttributeType:: BACKEND_TYPE_DATE     => FieldDescriptionInterface::TYPE_DATE,
        AbstractAttributeType:: BACKEND_TYPE_DATETIME => FieldDescriptionInterface::TYPE_DATETIME,
        AbstractAttributeType:: BACKEND_TYPE_DECIMAL  => FieldDescriptionInterface::TYPE_DECIMAL,
        AbstractAttributeType:: BACKEND_TYPE_INTEGER  => FieldDescriptionInterface::TYPE_INTEGER,
        AbstractAttributeType:: BACKEND_TYPE_OPTION   => FieldDescriptionInterface::TYPE_OPTIONS,
        AbstractAttributeType:: BACKEND_TYPE_TEXT     => FieldDescriptionInterface::TYPE_TEXT,
        AbstractAttributeType:: BACKEND_TYPE_VARCHAR  => FieldDescriptionInterface::TYPE_TEXT,
    );

    /**
     * @param FlexibleManager $flexibleManager
     */
    public function setFlexibleManager(FlexibleManager $flexibleManager)
    {
        $this->flexibleManager = $flexibleManager;

        // TODO: somehow get locale and scope from parameters interface
        $this->flexibleManager->setLocale('en');
        $this->flexibleManager->setScope('ecommerce');
    }

    /**
     * @return Attribute[]
     */
    protected function getFlexibleAttributes()
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
    protected function convertFlexibleTypeToFieldType($flexibleFieldType)
    {
        if (!isset(self::$typeMatches[$flexibleFieldType])) {
            throw new \LogicException('Unknown flexible backend field type.');
        }

        return self::$typeMatches[$flexibleFieldType];
    }
}
