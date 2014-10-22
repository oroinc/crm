<?php

namespace OroCRM\Bundle\TaskBundle\EventListener\Datagrid;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

class TaskListener
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Remove useless fields in case of filtering
     *
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();
        $parameters = $event->getDatagrid()->getParameters();

        if ($parameters->has('contactId')) {
            $this->removeColumn($config, 'contactName');
        }

        if ($parameters->has('accountId')) {
            $this->removeColumn($config, 'accountName');
        }
    }

    /**
     * @param DatagridConfiguration $config
     * @param string $fieldName
     */
    protected function removeColumn(DatagridConfiguration $config, $fieldName)
    {
        $config->offsetUnsetByPath(sprintf('[columns][%s]', $fieldName));
        $config->offsetUnsetByPath(sprintf('[filters][columns][%s]', $fieldName));
        $config->offsetUnsetByPath(sprintf('[sorters][columns][%s]', $fieldName));
    }

    /**
     * Add required filters
     *
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datagrid = $event->getDatagrid();
        /** @var OrmDatasource $ormDataSource */
        $ormDataSource = $datagrid->getDatasource();
        $queryBuilder = $ormDataSource->getQueryBuilder();
        $parameters = $datagrid->getParameters();

        if ($parameters->has('userId')) {
            $user = $this->entityManager->find('OroUserBundle:User', $parameters->get('userId'));
            $queryBuilder
                ->andWhere('task.owner = :user')
                ->setParameter('user', $user);
        }

        if ($parameters->has('taskIds')) {
            $taskIds = $parameters->get('taskIds');
            if (!is_array($taskIds)) {
                $taskIds = explode(',', $taskIds);
            }
            $queryBuilder->andWhere($queryBuilder->expr()->in('task.id', $taskIds));
        }
    }
}
