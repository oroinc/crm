<?php

namespace Oro\Bundle\SalesBundle\Autocomplete;

use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\ActivityBundle\Autocomplete\ContextSearchHandler;

use Oro\Bundle\SearchBundle\Query\Result\Item;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;

use Oro\Bundle\SalesBundle\Provider\Customer\CustomerIconProviderInterface;
use Oro\Bundle\SalesBundle\Provider\Customer\CustomerConfigProvider;

class CustomerSearchHandler extends ContextSearchHandler
{
    /** @var EntityRoutingHelper */
    protected $routingHelper;

    /** @var CustomerIconProviderInterface */
    protected $customerIconProvider;

    /** @var CustomerConfigProvider */
    protected $customerConfigProvider;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /**
     * @param CustomerIconProviderInterface $customerIconProvider
     */
    public function setCustomerIconProvider(CustomerIconProviderInterface $customerIconProvider)
    {
        $this->customerIconProvider = $customerIconProvider;
    }

    /**
     * @param CustomerConfigProvider $customerConfigProvider
     */
    public function setCustomerConfigProvider(CustomerConfigProvider $customerConfigProvider)
    {
        $this->customerConfigProvider = $customerConfigProvider;
    }

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function setDoctrineHelper(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param EntityRoutingHelper $routingHelper
     */
    public function setRoutingHelper(EntityRoutingHelper $routingHelper)
    {
        $this->routingHelper  = $routingHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function convertItems(array $items)
    {
        $groupedItems = $this->groupItemsByEntityName($items);

        $customers = [];
        foreach ($groupedItems as $entityName => $items) {
            $customers = array_merge(
                $customers,
                $this->objectManager
                    ->getRepository($entityName)
                    ->findBy(['id' => array_keys($items)])
            );
        }

        $result = [];
        foreach ($customers as $customer) {
            $identifier = $this->doctrineHelper->getSingleEntityIdentifier($customer);
            $item = $this->convertItem($groupedItems[ClassUtils::getClass($customer)][$identifier]);
            $item['icon'] = $this->customerIconProvider->getIcon($customer);
            $result[] = $item;
        }

        return $result;
    }

    /**
     * @param Item[] $items
     *
     * @return array
     */
    protected function groupItemsByEntityName(array $items)
    {
        $grouped = [];
        foreach ($items as $item) {
            $grouped[$item->getEntityName()][$item->getRecordId()] = $item;
        }

        return $grouped;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSearchAliases()
    {
        $customers = $this->customerConfigProvider->getCustomerClasses();

        return array_values($this->indexer->getEntityAliases($customers));
    }

    /**
     * {@inheritdoc}
     */
    protected function decodeTargets($targetsString)
    {
        return array_map(
            function ($item) {
                $item['entityClass'] = $this->routingHelper->resolveEntityClass($item['entityClass']);

                return $item;
            },
            parent::decodeTargets($targetsString)
        );
    }
}
