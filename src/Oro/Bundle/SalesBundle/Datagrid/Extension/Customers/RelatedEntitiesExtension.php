<?php

namespace Oro\Bundle\SalesBundle\Datagrid\Extension\Customers;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Exception\DatasourceException;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;

class RelatedEntitiesExtension extends AbstractExtension
{
    /** @var ConfigProvider */
    protected $customerConfigProvider;

    /** @var string */
    protected $relatedEntityClass;

    /**
     * @param ConfigProvider          $customerConfigProvider
     * @param string                  $relatedEntityClass
     */
    public function __construct(ConfigProvider $customerConfigProvider, $relatedEntityClass )
    {
        $this->customerConfigProvider = $customerConfigProvider;
        $this->relatedEntityClass = $relatedEntityClass;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        return
            $config->getDatasourceType() === OrmDatasource::TYPE &&
            $this->parameters->get('customer_class') &&
            $this->parameters->get('customer_id') &&
            $this->parameters->get('related_entity_class') === $this->relatedEntityClass &&
            $this->customerConfigProvider->isCustomerClass($this->parameters->get('customer_class'));
    }

    /**
     * {@inheritdoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        /** @var $datasource OrmDataSource */
        $customerClass    = $this->parameters->get('customer_class');
        $customerField    = $this->getCustomerField($customerClass);
        $queryBuilder     = $datasource->getQueryBuilder();
        $customerIdParam  = sprintf(':customerIdParam_%s', $customerField);
        $alias            = $this->getEntityAlias($queryBuilder);
        $customerAlias    = 'customer';
        $queryBuilder->join(sprintf('%s.customerAssociation', $alias), $customerAlias);
        $queryBuilder->andWhere(
            sprintf(
                '%s.%s = %s',
                $customerAlias,
                $customerField,
                $customerIdParam
            )
        );
        $queryBuilder->setParameter($customerIdParam, $this->parameters->get('customer_id'));
    }

    /**
     * @param QueryBuilder $qb
     *
     * @return string
     *
     * @throws DatasourceException
     */
    protected function getEntityAlias(QueryBuilder $qb)
    {
        $fromParts = $qb->getDQLPart('from');
        /** @var $fromPart Expr\From */
        foreach ($fromParts as $fromPart) {
            if ($fromPart->getFrom() === $this->relatedEntityClass) {
                return $fromPart->getAlias();
            }
        }

        throw new DatasourceException(
            sprintf(
                "Couldn't find %s alias in QueryBuilder.",
                $this->relatedEntityClass
            )
        );
    }

    /**
     * @param $customerClass
     *
     * @return string
     */
    protected function getCustomerField($customerClass)
    {
        return AccountCustomerManager::getCustomerTargetField($customerClass);
    }
}
