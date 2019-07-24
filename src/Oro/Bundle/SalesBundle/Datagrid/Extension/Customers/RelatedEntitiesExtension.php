<?php

namespace Oro\Bundle\SalesBundle\Datagrid\Extension\Customers;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Exception\DatasourceException;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;
use Oro\Component\DoctrineUtils\ORM\QueryBuilderUtil;

/**
 * Restrict related entities grid for customer.
 */
class RelatedEntitiesExtension extends AbstractExtension
{
    /** @var ConfigProvider */
    protected $customerConfigProvider;

    /** @var string */
    protected $relatedEntityClass;

    /**
     * @param ConfigProvider $customerConfigProvider
     * @param string         $relatedEntityClass
     */
    public function __construct(ConfigProvider $customerConfigProvider, $relatedEntityClass)
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
            parent::isApplicable($config)
            && $config->isOrmDatasource()
            && $this->parameters->get('customer_id')
            && $this->parameters->get('customer_class')
            && $this->parameters->get('related_entity_class') === $this->relatedEntityClass
            && $this->customerConfigProvider->isCustomerClass($this->parameters->get('customer_class'));
    }

    /**
     * {@inheritdoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        /** @var OrmDatasource $datasource */
        $customerClass    = $this->parameters->get('customer_class');
        $customerField    = $this->getCustomerField($customerClass);
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder     = $datasource->getQueryBuilder();
        $customerIdParam  = QueryBuilderUtil::sprintf(':customerIdParam_%s', $customerField);
        $alias            = $this->getEntityAlias($queryBuilder);
        $customerAlias    = 'customer';
        $queryBuilder->join(QueryBuilderUtil::getField($alias, 'customerAssociation'), $customerAlias);
        $queryBuilder->andWhere(
            $queryBuilder->expr()->eq(
                QueryBuilderUtil::getField($customerAlias, $customerField),
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
