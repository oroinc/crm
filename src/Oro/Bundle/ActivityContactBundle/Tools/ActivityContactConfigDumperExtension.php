<?php

namespace Oro\Bundle\ActivityContactBundle\Tools;

use Oro\Bundle\ActivityContactBundle\EntityConfig\ActivityScope;
use Oro\Bundle\ActivityContactBundle\Model\TargetExcludeList;
use Oro\Bundle\ActivityContactBundle\Provider\ActivityContactProvider;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityExtendBundle\Tools\DumperExtensions\AbstractEntityConfigDumperExtension;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendConfigDumper;

class ActivityContactConfigDumperExtension extends AbstractEntityConfigDumperExtension
{
    /** @var ConfigManager */
    protected $configManager;

    /** @var  ActivityContactProvider */
    protected $activityContactProvider;

    public function __construct(ConfigManager $configManager, ActivityContactProvider $activityContactProvider)
    {
        $this->configManager           = $configManager;
        $this->activityContactProvider = $activityContactProvider;
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
    public function preUpdate()
    {
        /** @var ConfigProvider $extendConfigProvider */
        $extendConfigProvider = $this->configManager->getProvider('extend');
        /** @var ConfigProvider $activityConfigProvider */
        $activityConfigProvider = $this->configManager->getProvider('activity');

        $contactingActivityClasses = $this->activityContactProvider->getSupportedActivityClasses();

        $entityConfigs = $extendConfigProvider->getConfigs();
        foreach ($entityConfigs as $entityConfig) {
            if ($entityConfig->is('is_extend')) {
                $entityClassName = $entityConfig->getId()->getClassName();
                // Skipp excluded entity
                if (TargetExcludeList::isExcluded($entityClassName)) {
                    continue;
                }

                /**
                 * Check if entity has any activity from contact activities group
                 */
                $entityActivities = $activityConfigProvider->getConfig($entityClassName)->get('activities');
                if (!$entityActivities
                    || !array_intersect($contactingActivityClasses, $entityActivities)
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
                 * Check if entity already has all needed fields.
                 * If at least one is not present we should check and add it too.
                 */
                if (false === (bool) array_diff(
                    array_keys(ActivityScope::$fieldsConfiguration),
                    array_intersect($entityFieldNames, array_keys(ActivityScope::$fieldsConfiguration))
                )) {
                    continue;
                }

                foreach (ActivityScope::$fieldsConfiguration as $fieldName => $fieldConfig) {
                    if (!in_array($fieldName, $entityFieldNames)) {
                        $this->configManager->createConfigFieldModel(
                            $entityClassName,
                            $fieldName,
                            $fieldConfig['type'],
                            $fieldConfig['mode']
                        );

                        $this->updateConfigs($entityClassName, $fieldName, $fieldConfig['options']);
                    }
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
            $config     = $this->configManager->getProvider($scope)->getConfig($className, $fieldName);
            $hasChanges = false;
            foreach ($scopeValues as $code => $val) {
                if (!$config->is($code, $val)) {
                    $config->set($code, $val);
                    $hasChanges = true;
                }
            }
            if ($hasChanges) {
                $this->configManager->persist($config);
            }
        }
    }
}
