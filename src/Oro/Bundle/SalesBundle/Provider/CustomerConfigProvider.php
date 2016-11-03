<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class CustomerConfigProvider
{
    /** @var ConfigManager */
    protected $configManager;

    /** @var ConfigProvider */
    protected $entityConfigProvider;

    /** @var ConfigProvider */
    protected $gridConfigProvider;

    /**
     * @param ConfigManager  $configManager
     * @param ConfigProvider $entityConfigProvider
     * @param ConfigProvider $gridConfigProvider
     */
    public function __construct(
        ConfigManager $configManager,
        ConfigProvider $entityConfigProvider,
        ConfigProvider $gridConfigProvider
    ) {
        $this->configManager = $configManager;
        $this->entityConfigProvider = $entityConfigProvider;
        $this->gridConfigProvider = $gridConfigProvider;
    }

    /**
     * @param string $ownerClass
     *
     * @return array
     * [
     *     entityClass => propertyName,
     *     ...
     * ]
     */
    public function getCustomerProperties($ownerClass)
    {
        // todo: retrieve from config
        return [
            'Oro\Bundle\MagentoBundle\Entity\Customer' => 'customer1c6b2c05'
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
    public function getCustomersData($ownerClass)
    {
        $customerClasses = array_keys($this->getCustomerProperties($ownerClass));
        $result = [];
        foreach ($customerClasses as $customerClass) {
            $result[] = [
                'label'       => $this->entityConfigProvider->getConfig($customerClass)->get('label'),
                'icon'        => $this->entityConfigProvider->getConfig($customerClass)->get('icon'),
                'className'   => $customerClass,
                'gridName'    => $this->getCustomerGridByEntity($customerClass),
                'routeCreate' => $this->getRouteCreate($customerClass),
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
    public function getCustomerGridByEntity($entityClass)
    {
        if (ExtendHelper::isCustomEntity($entityClass)) {
            return 'custom-entity-grid';
        }

        $config = $this->gridConfigProvider->getConfig($entityClass);

        return $config->get('default');
    }

    /**
     * @param string $entityClass
     *
     * @return string
     * @throws \RuntimeException
     */
    protected function getRouteCreate($entityClass)
    {
        $metadata = $this->configManager->getEntityMetadata($entityClass);
        if (!$metadata) {
            throw new \RuntimeException(sprintf('Cannot find EntityMetadata for class %s', $entityClass));
        }

        if (!$metadata->routeCreate) {
            throw new \RuntimeException(sprintf('routeCreate is not configured for class %s', $entityClass));
        }

        return $metadata->routeCreate;
    }
}
