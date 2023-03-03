<?php

namespace Oro\Bundle\ActivityContactBundle\Provider;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\ActivityContactBundle\EntityConfig\ActivityScope;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Provide data for entity activity contact.
 */
class EntityActivityContactDataProvider
{
    /** @var ConfigProvider */
    protected $extendProvider;

    /** @var PropertyAccessor */
    protected $propertyAccessor;

    public function __construct(ConfigManager $configManager)
    {
        $this->extendProvider   = $configManager->getProvider('extend');
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param object $object
     *
     * @return array
     */
    public function getEntityContactData($object)
    {
        $activityContactConfigs = $this->getEntityActivityContactFields($object);

        return array_reduce(
            $activityContactConfigs,
            function ($carry, ConfigInterface $item) use ($object) {
                /** @var FieldConfigId $fieldConfigId */
                $fieldConfigId     = $item->getId();
                $fieldName         = $fieldConfigId->getFieldName();
                $carry[$fieldName] = $this->propertyAccessor->getValue($object, $fieldName);

                return $carry;
            },
            []
        );
    }

    /**
     * @param object $entity
     *
     * @return ConfigInterface[]
     */
    protected function getEntityActivityContactFields($entity)
    {
        $fields = array_keys(ActivityScope::$fieldsConfiguration);

        return $this->extendProvider->filter(
            function (ConfigInterface $config) use ($fields) {
                /** @var FieldConfigId $fieldConfigId */
                $fieldConfigId = $config->getId();

                return in_array($fieldConfigId->getFieldName(), $fields);
            },
            ClassUtils::getClass($entity)
        );
    }
}
