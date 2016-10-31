<?php

namespace Oro\Bundle\SalesBundle\Autocomplete;

use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\ActivityBundle\Autocomplete\ContextSearchHandler;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\SearchBundle\Query\Result\Item;
use Oro\Bundle\EntityBundle\Provider\EntityAvatarProviderInterface;

class OpportunityCustomerSearchHandler extends ContextSearchHandler
{
    /** @var ConfigProvider */
    protected $salesConfigProvider;

    /** @var EntityAvatarProviderInterface */
    protected $entityAvatarProvider;

    /**
     * @param ConfigProvider $salesConfigProvider
     */
    public function setSalesConfigProvider(ConfigProvider $salesConfigProvider)
    {
        $this->salesConfigProvider = $salesConfigProvider;
    }

    /**
     * @param EntityAvatarProviderInterface $entityAvatarProvider
     */
    public function setEntityAvatarProvider(EntityAvatarProviderInterface $entityAvatarProvider)
    {
        $this->entityAvatarProvider = $entityAvatarProvider;
    }

    /**
     * {@inheritdoc}
     */
    protected function convertItems(array $items)
    {
        $grouppedItems = $this->groupItemsByEntityName($items);

        $customers = [];
        foreach ($grouppedItems as $entityName => $items) {
            $customers = array_merge(
                $customers,
                $this->objectManager
                    ->getRepository($entityName)
                    ->findById(array_keys($items))
            );
        }

        $result = [];
        foreach ($customers as $customer) {
            $item = $this->convertItem($grouppedItems[ClassUtils::getClass($customer)][$customer->getId()]);
            $item['avatar'] = $this->entityAvatarProvider->getAvatarImage('avatar_xsmall', $customer);
            $result[] = $item;
        }

        return $result;
    }

    /**
     * @param Item[] $items
     *
     * @return Item[]
     */
    protected function groupItemsByEntityName(array $items)
    {
        $groupped = [];
        foreach ($items as $item) {
            $groupped[$item->getEntityName()][$item->getRecordId()] = $item;
        }

        return $groupped;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSearchAliases()
    {
        // todo: read $customers value from config provider
        $customers = [
            'Oro\Bundle\MagentoBundle\Entity\Customer' => 'customer1c6b2c05',
        ];

        return array_values($this->indexer->getEntityAliases(array_keys($customers)));
    }
}
