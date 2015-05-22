<?php

namespace OroCRM\Bundle\ActivityContactBundle\Provider;

use Doctrine\Common\Util\ClassUtils;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProviderInterface;
use Oro\Bundle\UIBundle\Provider\WidgetProviderInterface;

use OroCRM\Bundle\ActivityContactBundle\EntityConfig\ActivityScope;

class EntityActivityContactDataProvider
{
    /** @var ConfigProviderInterface */
    protected $entityProvider;

    /** @var ConfigProviderInterface */
    protected $extendProvider;

    /** @var PropertyAccessor */
    protected $propertyAccessor;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->entityProvider   = $configManager->getProvider('entity');
        $this->extendProvider   = $configManager->getProvider('extend');
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
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
                $carry[$fieldName] = [
                    'label' => $this->entityProvider->getConfigById($fieldConfigId)->get('label'),
                    'value' => $this->propertyAccessor->getValue($object, $fieldName)
                ];

                return $carry;
            },
            []
        );
    }

    /**
     * @param $entity
     * @return array|ConfigInterface[]
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
