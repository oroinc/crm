<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\EntityConfigBundle\Config\Config;

use Oro\Bundle\SecurityBundle\SecurityFacade;

class CustomerConfigProvider
{
    /** @var EntityRoutingHelper */
    protected $routingHelper;

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var ConfigManager */
    protected $configManager;

    protected $configs = [
        'Oro\Bundle\SalesBundle\Entity\Lead'        => 'lead',
        'Oro\Bundle\SalesBundle\Entity\Opportunity' => 'opportunity',
    ];

    /**
     * @param EntityRoutingHelper $routingHelper
     * @param SecurityFacade      $securityFacade
     * @param ConfigManager       $configManager
     */
    public function __construct(
        EntityRoutingHelper $routingHelper,
        SecurityFacade $securityFacade,
        ConfigManager $configManager
    ) {
        $this->routingHelper  = $routingHelper;
        $this->securityFacade = $securityFacade;
        $this->configManager  = $configManager;
    }

    /**
     * @param string $ownerClass
     *
     * @return array
     */
    public function getAssociatedCustomerClasses($ownerClass)
    {
        $scope = $this->configs[$ownerClass];

        $classes = [];
        /** @var Config[] $configs */
        $configs = $this->configManager->getProvider($scope)->getConfigs();
        foreach ($configs as $config) {
            if ($config->is('enabled')) {
                $classes[] = $config->getId()->getClassName();
            }
        }

        return $classes;
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
            $routeCreate = $this->getRouteCreate($class);
            $result[] = [
                'className'   => $this->routingHelper->getUrlSafeClassName($class),
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
