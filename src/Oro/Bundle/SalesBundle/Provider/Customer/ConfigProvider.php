<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

/**
 * Provides customer configuration, such as registered customer classes, entity label, icon, route, etc.
 */
class ConfigProvider
{
    /** @var ConfigManager */
    protected $configManager;

    /** @var ConfigCache */
    private $cache;

    public function __construct(ConfigManager $configManager, ConfigCache $cache)
    {
        $this->configManager = $configManager;
        $this->cache = $cache;
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

        if (is_object($objectOrClass)) {
            $objectOrClass = ClassUtils::getClass($objectOrClass);
        }

        return in_array($objectOrClass, $this->getCustomerClasses(), true);
    }

    /**
     * @return string[]
     */
    public function getCustomerClasses()
    {
        $cacheKey = $this->getCacheKey();
        $customerClasses = $this->cache->getClasses($cacheKey);
        if (null === $customerClasses) {
            $customerClasses = $this->getAssociatedCustomerClasses();
            $this->cache->setClasses($cacheKey, $customerClasses);
        }

        return $customerClasses;
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
     * @return string[]
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

    /**
     * @return string
     */
    protected function getCacheKey()
    {
        return 'default';
    }
}
