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

        if (is_object($objectOrClass)) {
            $objectOrClass = ClassUtils::getClass($objectOrClass);
        }

        return in_array($objectOrClass, $this->getAssociatedCustomerClasses(), true);
    }

    public function getCustomerClasses()
    {
        return $this->getAssociatedCustomerClasses();
    }

    /**
     * @return array
     * [
     *     className   => customer class with _ instead of \,
     *     label       => entity label,
     *     icon        => entity icon,
     *     gridName    => customer grid name
     *     routeCreate => route to create entity
     * ]
     */
    public function getCustomersData()
    {
        $result = [];

        $customerClasses = $this->getCustomerClasses();
        foreach ($customerClasses as $class) {
            $result[] = [
                'className'   => $class,
                'label'       => $this->getLabel($class),
                'icon'        => $this->getIcon($class),
                'gridName'    => $this->getGrid($class),
                'routeCreate' => $this->getRouteCreate($class),
            ];
        }

        return $result;
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
    protected function getIcon($entityClass)
    {
        return $this->configManager->getEntityConfig('entity', $entityClass)->get('icon');
    }

    /**
     * @param string $entityClass
     *
     * @return string|null
     */
    protected function getRouteCreate($entityClass)
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
