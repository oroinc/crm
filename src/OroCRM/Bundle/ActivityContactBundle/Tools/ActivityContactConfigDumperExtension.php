<?php

namespace OroCRM\Bundle\ActivityContactBundle\Tools;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendConfigDumper;
use Oro\Bundle\EntityExtendBundle\Tools\DumperExtensions\AbstractEntityConfigDumperExtension;

use OroCRM\Bundle\ActivityContactBundle\EntityConfig\ActivityScope;

class ActivityContactConfigDumperExtension extends AbstractEntityConfigDumperExtension
{
    /** @var ConfigManager */
    protected $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($actionType)
    {
        return $actionType === ExtendConfigDumper::ACTION_PRE_UPDATE;
    }

    /**
     * {@inheritdoc}
     */
    //public function postUpdate()
    public function preUpdate()
    {
        /** @var ConfigProvider $extendConfigProvider */
        $extendConfigProvider = $this->configManager->getProvider('extend');
        /** @var ConfigProvider $activityConfigProvider */
        $activityConfigProvider = $this->configManager->getProvider('activity');

        $entityConfigs = $extendConfigProvider->getConfigs();
        foreach ($entityConfigs as $entityConfig) {
            if ($entityConfig->is('is_extend')) {
                $entityClassName = $entityConfig->getId()->getClassName();

                /**
                 * Check if entity has any activity from contact activities group
                 */
                $entityActivities = $activityConfigProvider->getConfig($entityClassName)->get('activities');
                if ($entityActivities
                    && !array_intersect(ActivityScope::$contactingActivityClasses, $entityActivities)
                ) {
                    continue;
                }

                /** @var ConfigInterface[] $entityFields */
                $entityFields     = $extendConfigProvider->getConfigs($entityClassName);
                $entityFieldNames = array_map(
                    function (ConfigInterface $item) {
                        return $item->getId()->getFieldName();
                    },
                    $entityFields
                );
                /**
                 * Check if entity already has needed fields
                 */
                if (array_intersect($entityFieldNames, array_keys(ActivityScope::$fieldsConfiguration))) {
                    continue;
                }

                foreach (ActivityScope::$fieldsConfiguration as $fieldName => $fieldConfig) {
                    $this->configManager->createConfigFieldModel(
                        $entityClassName,
                        $fieldName,
                        $fieldConfig['type'],
                        ConfigModelManager::MODE_READONLY
                    );

                    $this->updateConfigs($entityClassName, $fieldName, $fieldConfig['options']);
                }
            }
        }
    }

    /**
     * @param string $className
     * @param array  $options
     * @param string $fieldName
     */
    protected function updateConfigs($className, $fieldName, $options)
    {
        foreach ($options as $scope => $scopeValues) {
            $configProvider = $this->configManager->getProvider($scope);
            $config         = $configProvider->getConfig($className, $fieldName);
            $hasChanges     = false;
            foreach ($scopeValues as $code => $val) {
                if (!$config->is($code, $val)) {
                    $config->set($code, $val);
                    $hasChanges = true;
                }
            }
            if ($hasChanges) {
                $configProvider->persist($config);
            }
        }
    }
}
