<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer;

use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SecurityBundle\SecurityFacade;

class ConfigProvider
{
    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var ConfigManager */
    protected $configManager;

    /**
     * @param SecurityFacade $securityFacade
     * @param ConfigManager  $configManager
     */
    public function __construct(SecurityFacade $securityFacade, ConfigManager $configManager)
    {
        $this->securityFacade = $securityFacade;
        $this->configManager  = $configManager;
    }

    /**
     * @param object|string|null $objectOrClass
     *
     * @return bool
     */
    public function isCustomerClass($objectOrClass)
    {
        if (!$objectOrClass) {
            return false;
        }

        $class = is_object($objectOrClass) ? ClassUtils::getClass($objectOrClass) : $objectOrClass;

        return in_array($class, $this->getAssociatedCustomerClasses());
    }

    public function getCustomerClasses()
    {
        return $this->getAssociatedCustomerClasses();
    }

    /**
     * @return array
     * [
     *     className => customer class with _ instead of \,
     *     label => entity label,
     *     icon  => entity icon,
     *     gridName  => customer grid name
     *     routeCreate => route to create entity
     *     first => should be shown by default?
     * ]
     */
    public function getCustomersData()
    {
        $result = [];

        $customerClasses = $this->getCustomerClasses();
        foreach ($customerClasses as $class) {
            $routeCreate = $this->getRouteCreate($class);
            $result[]    = [
                'className'   => $class,
                'label'       => $this->getLabel($class),
                'icon'        => $this->getIcon($class),
                'gridName'    => $this->getDefaultGrid($class),
                'routeCreate' => $this->securityFacade->isGranted($routeCreate) ? $routeCreate : null,
                'first'       => !$result,
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
    public function getDefaultGrid($entityClass)
    {
        if (ExtendHelper::isCustomEntity($entityClass)) {
            return 'custom-entity-grid';
        }

        $config = $this->configManager->getProvider('grid')->getConfig($entityClass);

        return $config->get('default');
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
     * @return array
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
