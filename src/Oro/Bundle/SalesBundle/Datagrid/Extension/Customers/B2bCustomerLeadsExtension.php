<?php

namespace Oro\Bundle\SalesBundle\Datagrid\Extension\Customers;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;

class B2bCustomerLeadsExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        return
            $config->getDatasourceType() === OrmDatasource::TYPE &&
            $this->parameters->get('business_customer_id');
    }

    /**
     * {@inheritdoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        /** @var $datasource OrmDataSource */
        $customerField    = $this->getCustomerField();
        $queryBuilder     = $datasource->getQueryBuilder();
        $customerIdParam  = sprintf(':customerIdParam_%s', $customerField);
        $leadAlias = $this->getLeadAlias($queryBuilder);
        $customerAlias    = 'customer';
        $queryBuilder->join(sprintf('%s.customerAssociation', $leadAlias), $customerAlias);
        $queryBuilder->andWhere(
            sprintf(
                '%s.%s = %s',
                $customerAlias,
                $customerField,
                $customerIdParam
            )
        );
        $queryBuilder->setParameter($customerIdParam, $this->parameters->get('business_customer_id'));
    }

    /**
     * {@inheritdoc}
     */
    protected function getCustomerField()
    {
        return ExtendHelper::buildAssociationName(B2bCustomer::class, CustomerScope::ASSOCIATION_KIND);
    }

    /**
     * @param QueryBuilder $qb
     *
     * @return string
     *
     * @throws DatasourceException
     */
    protected function getLeadAlias(QueryBuilder $qb)
    {
        $fromParts = $qb->getDQLPart('from');
        /** @var $fromPart Expr\From */
        foreach ($fromParts as $fromPart) {
            if ($fromPart->getFrom() === Lead::class) {
                return $fromPart->getAlias();
            }
        }

        throw new DatasourceException('Couldn\'t find Lead\'s alias in QueryBuilder.');
    }
}
