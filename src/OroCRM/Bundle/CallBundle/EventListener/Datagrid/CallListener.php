<?php

namespace OroCRM\Bundle\CallBundle\EventListener\Datagrid;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

class CallListener
{
    /**
     * @var RequestParameters
     */
    protected $requestParameters;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param RequestParameters $requestParameters
     * @param EntityManager $entityManager
     */
    public function __construct(RequestParameters $requestParameters, EntityManager $entityManager)
    {
        $this->requestParameters = $requestParameters;
        $this->entityManager = $entityManager;
    }

    /**
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        /** @var OrmDatasource $ormDataSource */
        $ormDataSource = $event->getDatagrid()->getDatasource();
        $queryBuilder = $ormDataSource->getQueryBuilder();

        $userId = $this->requestParameters->get('userId', null);
        if ($userId) {
            $user = $this->entityManager->find('OroUserBundle:User', $userId);
            $queryBuilder
                ->andWhere('call.owner = :user')
                ->setParameter('user', $user);
        }

        $contactId = $this->requestParameters->get('contactId', null);
        if ($contactId) {
            $contact = $this->entityManager->find('OroCRMContactBundle:Contact', $contactId);
            $queryBuilder
                ->andWhere('call.relatedContact = :contact')
                ->setParameter('contact', $contact);
        }
    }
}
