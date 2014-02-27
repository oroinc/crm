<?php

namespace OroCRM\Bundle\AccountBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;

use Oro\Bundle\EmailBundle\Datagrid\EmailQueryFactory;
use Oro\Bundle\EmailBundle\Entity\Util\EmailUtil;

class AccountEmailGridListener
{
    /** @var EmailQueryFactory */
    protected $queryFactory;

    /** @var RequestParameters */
    protected $requestParams;

    /** @var  EntityManager */
    protected $em;

    public function __construct(
        RequestParameters $requestParams,
        EntityManager $em,
        EmailQueryFactory $factory
    ) {
        $this->requestParams = $requestParams;
        $this->em = $em;
        $this->queryFactory = $factory;
    }

    /**
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $dataSource = $event->getDatagrid()->getDatasource();
        if ($dataSource instanceof OrmDatasource) {
            /** @var QueryBuilder $query */
            $queryBuilder = $dataSource->getQueryBuilder();

            $this->queryFactory->prepareQuery($queryBuilder);

            $emails = [];

            if ($id = $this->requestParams->get('accountId')) {
                $account = $this->em
                    ->getRepository('OroCRMAccountBundle:Account')
                    ->find($id);

                if (method_exists($account, 'getExtendEmail')) {
                    if ($email = $account->getExtendEmail()) {
                        $emails = EmailUtil::extractEmailAddresses($email);
                    }
                }
            }

            $queryBuilder->setParameter(
                'email_addresses',
                !empty($emails) ? $emails : null
            );
        }
    }
}
