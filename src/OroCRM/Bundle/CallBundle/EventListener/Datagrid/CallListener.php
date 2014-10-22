<?php

namespace OroCRM\Bundle\CallBundle\EventListener\Datagrid;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

class CallListener
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
                ->andWhere('call.owner = :user')
                ->setParameter('user', $user);
        }

        if ($parameters->has('callIds')) {
            $callIds = $parameters->get('callIds');
            if (!is_array($callIds)) {
                $callIds = explode(',', $callIds);
            }
            $queryBuilder->andWhere($queryBuilder->expr()->in('call.id', $callIds));
        }
    }
}
