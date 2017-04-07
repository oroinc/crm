<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer;

use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

class ConfigProvider
{
    const GRID_KEY = 'context';

    /** @var ConfigManager */
    protected $configManager;

    /** @var string */
    private $customerClasses;

    /**
     * @param ConfigManager    $configManager
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
     */
    public function getCustomerClasses()
    {
        if (null === $this->customerClasses) {
            $this->customerClasses = $this->getAssociatedCustomerClasses();
        }

        return $this->customerClasses;
    }

    /**
     * @return array
     * [
     *     className   => FQCN of a customer,
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
            $routeCreate = $this->getRouteCreate($class);
            $defaultGrid = $this->getGrid($class);
            $result[]    = [
                'className'       => $class,
                'label'           => $this->getLabel($class),
                'icon'            => $this->getIcon($class),
                'gridName'        => $defaultGrid,
                'routeCreate'     => $routeCreate,
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
        return $this->configManager->getProvider('entity')->getConfig($entityClass)->get('label');
    }

    /**
     * @param string $entityClass
     *
     * @return string|null
     */
    public function getGrid($entityClass)
    {
        $config = $this->configManager->getProvider('grid')->getConfig($entityClass);

        return $config->get(self::GRID_KEY);
    }

    /**
     * @param string $entityClass
     *
     * @return string|null
     */
    protected function getIcon($entityClass)
    {
        return $this->configManager->getProvider('entity')->getConfig($entityClass)->get('icon');
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
     * @return string[]
     */
    protected function getAssociatedCustomerClasses()
    {
        $classes = [];
        /** @var Config[] $configs */
        $configs = $this->configManager->getProvider('customer')->getConfigs();
        foreach ($configs as $config) {
            if ($config->is('enabled')) {
                $classes[] = $config->getId()->getClassName();
            }
        }

        return $classes;
    }
}
