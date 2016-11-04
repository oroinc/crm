<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

// @todo: Recheck and probably rename it.
class CustomerConfigProvider
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
     * @param string $ownerClass - e.g Lead or Opportunity
     *
     * @return array
     */
    public function getAssociatedCustomerClasses($ownerClass)
    {
        // @todo: Add functionality and fetch this data from config manager
        return [
            'Oro\Bundle\MagentoBundle\Entity\Customer',
        ];
    }

    /**
     * @param string $ownerClass
     *
     * @return array
     * [
     *     label => entity label,
     *     icon  => entity icon,
     *     className => customer class
     *     gridName  => customer grid name
     *     first => should be shown by default?
     * ]
     */
    public function getData($ownerClass)
    {
        $result = [];

        $customerClasses = $this->getAssociatedCustomerClasses($ownerClass);
        foreach ($customerClasses as $class) {
            $result[] = [
                'className'   => $class,
                'label'       => $this->getLabel($class),
                'icon'        => $this->getIcon($class),
                'gridName'    => $this->getDefaultGrid($class),
                'routeCreate' => $this->getRouteCreate($class),
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
    protected function getLabel($entityClass)
    {
        return $this->configManager->getProvider('entity')->getConfig($entityClass)->get('label');
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
    protected function getRouteCreate($entityClass)
    {
        $metadata = $this->configManager->getEntityMetadata($entityClass);
        if ($metadata && $metadata->routeCreate) {
            return $metadata->routeCreate;
        }

        return null;
    }
}
