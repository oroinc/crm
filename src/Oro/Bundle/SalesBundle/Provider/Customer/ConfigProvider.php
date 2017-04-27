<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer;

use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

class ConfigProvider
{
    /** @var ConfigManager */
    protected $configManager;

    /** @var string */
    private $customerClasses;

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

        return in_array($objectOrClass, $this->getCustomerClasses(), true);
    }

    /**
     * @return string[]
     * [
     *     className   => FQCN of a customer,
     *     label       => entity label,
     *     icon        => entity icon,
     *     gridName    => customer grid name
     *     routeCreate => route to create entity
     * ]
     */
    public function getCustomerClasses()
    {
        if (null === $this->customerClasses) {
            $this->customerClasses = $this->getAssociatedCustomerClasses();
        }

        return $this->customerClasses;
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
}
