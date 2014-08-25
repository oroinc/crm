<?php

namespace OroCRM\Bundle\MarketingListBundle\Datagrid;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\From;

use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Model\DataGridConfigurationHelper;

class DataGridRemovedItemsListener
{
    const MARKETING_LIST_REMOVED_ITEM_ENTITY = 'OroCRMMarketingListBundle:MarketingListRemovedItem';
    const MARKETING_LIST_ENTITY = 'OroCRMMarketingListBundle:MarketingList';
    const REMOVED_ITEMS_EXTENDER_GRID_NAME = 'orocrm-marketing-list-removed-items-extend';

    /**
     * @var ContainerInterface
     */
    protected $container;

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
     * @param ContainerInterface $container
     * @param DataGridConfigurationHelper $dataGridConfigurationHelper
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        DoctrineHelper $doctrineHelper,
        ContainerInterface $container,
        DataGridConfigurationHelper $dataGridConfigurationHelper
    ) {
        $this->container = $container;
        $this->doctrineHelper = $doctrineHelper;
        $this->managerRegistry = $managerRegistry;
        $this->dataGridConfigurationHelper = $dataGridConfigurationHelper;
    }

    /**
     * @param PreBuild $event
     */
    public function onPreBuild(PreBuild $event)
    {
        $marketingListId = $this->getMarketingListId();

        if ($marketingListId) {
            $marketingList = $this->getMarketingListById($marketingListId);

            if ($marketingList) {
                $this->dataGridConfigurationHelper->extendConfiguration(
                    $event->getConfig(),
                    self::REMOVED_ITEMS_EXTENDER_GRID_NAME
                );
            }
        }
    }

    /**
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $marketingListId = $this->getMarketingListId();

        if ($marketingListId) {
            $marketingList = $this->getMarketingListById($marketingListId);

            if ($marketingList) {
                $datagrid = $event->getDatagrid();
                $dataSource = $datagrid->getDatasource();

                if ($dataSource instanceof OrmDatasource) {
                    $entity = $marketingList->getEntity();
                    $idName = $this->doctrineHelper->getSingleEntityIdentifierFieldName($marketingList->getEntity());
                    $queryBuilder = $dataSource->getQueryBuilder();
                    $queryBuilder->addSelect($marketingListId . ' as marketingList');

                    /** @var EntityManager $removedItemsManager */
                    $removedItemsManager = $this->managerRegistry
                        ->getManagerForClass(self::MARKETING_LIST_REMOVED_ITEM_ENTITY);
                    $removedItemsQueryBuilder = $removedItemsManager->createQueryBuilder();
                    $removedItemsQueryBuilder->select('_rm.entityId')
                        ->from(self::MARKETING_LIST_REMOVED_ITEM_ENTITY, '_rm')
                        ->where('_rm.marketingList = :marketingList');

                    /** @var From[] $from */
                    $from = $queryBuilder->getDQLPart('from');
                    foreach ($from as $fromPart) {
                        if ($fromPart->getFrom() === $entity) {
                            $queryBuilder->andWhere(
                                $queryBuilder->expr()->in(
                                    $fromPart->getAlias() . '.' . $idName,
                                    $removedItemsQueryBuilder->getDQL()
                                )
                            );
                            $queryBuilder->setParameter('marketingList', $marketingList);
                            break;
                        }
                    }
                }
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
     * @return int
     */
    protected function getMarketingListId()
    {
        /** @var Request $request */
        $request = $this->container->get('request');
        $gridName = $request->attributes->get('gridName');

        if ($gridName) {
            $gridParameters = $request->get($gridName);
            if (isset($gridParameters['marketing_list_id'])) {
                return (int)$gridParameters['marketing_list_id'];
            }
        } elseif ($request->attributes->get('_route') == 'orocrm_marketing_list_update') {
            return (int)$request->attributes->get('id');
        }

        return null;
    }
}
