<?php

namespace OroCRM\Bundle\MarketingListBundle\Datagrid;

use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\ManagerRegistry;

use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Model\DataGridConfigurationHelper;

class MarketingListRemovedItemsListener
{
    const MARKETING_LIST_REMOVED_ITEM_ENTITY = 'OroCRMMarketingListBundle:MarketingListRemovedItem';
    const MARKETING_LIST_ENTITY = 'OroCRMMarketingListBundle:MarketingList';
    const REMOVED_ITEMS_MIXIN_NAME = 'orocrm-marketing-list-removed-items-mixin';

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var DataGridConfigurationHelper
     */
    protected $dataGridConfigurationHelper;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param DoctrineHelper $doctrineHelper
     * @param DataGridConfigurationHelper $dataGridConfigurationHelper
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        DoctrineHelper $doctrineHelper,
        DataGridConfigurationHelper $dataGridConfigurationHelper
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->managerRegistry = $managerRegistry;
        $this->dataGridConfigurationHelper = $dataGridConfigurationHelper;
    }

    /**
     * @param PreBuild $event
     */
    public function onPreBuild(PreBuild $event)
    {
        $marketingListId = $this->getMarketingListId($event->getParameters());

        if ($marketingListId) {
            $marketingList = $this->getMarketingListById($marketingListId);

            if ($marketingList) {
                $config = $event->getConfig();
                $entity = $marketingList->getEntity();
                $marketingListId = (int)$marketingList->getId();
                $removedItemAlias = '_mlri';
                $idName = $this->doctrineHelper->getSingleEntityIdentifierFieldName($marketingList->getEntity());

                $from = $config->offsetGetByPath('[source][query][from]');
                foreach ($from as $table) {
                    if ($table['table'] === $entity) {
                        // Add marketingList id to select to be able to create correct URLs
                        $config->offsetAddToArrayByPath(
                            '[source][query][select]',
                            array(
                                $marketingListId . ' as marketingList'
                            )
                        );

                        // Inner join removed items to restrict list only to removed
                        $joinCondition = $removedItemAlias . '.entityId = ' . $table['alias'] . '.' . $idName
                            . ' AND ' . $removedItemAlias . '.marketingList = ' . $marketingListId;
                        $config->offsetAddToArrayByPath(
                            '[source][query][join][inner]',
                            array(
                                array(
                                    'join' => self::MARKETING_LIST_REMOVED_ITEM_ENTITY,
                                    'alias' => $removedItemAlias,
                                    'conditionType' => 'WITH',
                                    'condition' => $joinCondition
                                )
                            )
                        );
                        break;
                    }
                }

                // Apply mixin
                $this->dataGridConfigurationHelper->extendConfiguration(
                    $event->getConfig(),
                    self::REMOVED_ITEMS_MIXIN_NAME
                );
            }
        }
    }

    /**
     * @param int $id
     * @return MarketingList
     */
    protected function getMarketingListById($id)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->managerRegistry->getManagerForClass(self::MARKETING_LIST_ENTITY);
        return $entityManager->find(self::MARKETING_LIST_ENTITY, $id);
    }

    /**
     * @param ParameterBag $parameters
     * @return int
     */
    protected function getMarketingListId(ParameterBag $parameters)
    {
        return (int)$parameters->get('marketing_list_id', null);
    }
}
