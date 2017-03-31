<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

class ConfigProvider
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
     * @param object|string $objectOrClass
     *
     * @return bool
     */
    public function isCustomerClass($objectOrClass)
    {
        if (!$objectOrClass) {
            return false;
        }

        $class = is_object($objectOrClass) ? ClassUtils::getClass($objectOrClass) : $objectOrClass;

        return in_array($class, $this->getAssociatedCustomerClasses(), true);
    }

    /**
     * @return string[]
     */
    public function getCustomerClasses()
    {
        return $this->getAssociatedCustomerClasses();
    }

    /**
     * @param string $entityClass
     *
     * @return string|null
     */
    public function getLabel($entityClass)
    {
        return $this->configManager->getEntityConfig('entity', $entityClass)->get('label');
    }

    /**
     * @param string $entityClass
     *
     * @return string|null
     */
    public function getGrid($entityClass)
    {
        return $this->configManager->getEntityConfig('grid', $entityClass)->get('context');
    }

    /**
     * @param string $entityClass
     *
     * @return string|null
     */
    public function getIcon($entityClass)
    {
        return $this->configManager->getEntityConfig('entity', $entityClass)->get('icon');
    }

    /**
     * @param string $entityClass
     *
     * @return string|null
     */
    public function getRouteCreate($entityClass)
    {
        $metadata = $this->configManager->getEntityMetadata($entityClass);
        if ($metadata && $metadata->routeCreate) {
            return $metadata->routeCreate;
        }

        return null;
    }

    /**
     * @return array
     */
    protected function getAssociatedCustomerClasses()
    {
        $classes = [];
        /** @var Config[] $configs */
        $configs = $this->configManager->getConfigs('customer');
        foreach ($configs as $config) {
            if ($config->is('enabled')) {
                $classes[] = $config->getId()->getClassName();
            }
        }

        return $classes;
    }
}
