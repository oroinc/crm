<?php

namespace Oro\Bundle\SalesBundle\Datagrid\Extension\Customers;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Exception\DatasourceException;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;

class OpportunitiesExtension extends AbstractExtension
{
    /** @var ConfigProvider */
    protected $opportunityProvider;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->opportunityProvider = $configManager->getProvider('opportunity');
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        return
            $config->getDatasourceType() === OrmDatasource::TYPE &&
            $this->parameters->has('customer_class') &&
            $this->parameters->has('customer_id') &&
            $this->parameters->get('customer_class') &&
            $this->parameters->get('customer_id') &&
            $this->opportunityProvider->hasConfig($this->parameters->get('customer_class')) &&
            $this->opportunityProvider->getConfig($this->parameters->get('customer_class'))->is('enabled');
    }

    /**
     * {@inheritdoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        /** @var $datasource OrmDataSource */
        $customerClass = $this->parameters->get('customer_class');
        $customerField    = ExtendHelper::buildAssociationName(
            $customerClass,
            CustomerScope::ASSOCIATION_KIND
        );
        $queryBuilder     = $datasource->getQueryBuilder();
        $customerIdParam  = sprintf(':customerIdParam_%s', $customerField);
        $opportunityAlias = $this->getOpportunityAlias($queryBuilder);
        $queryBuilder->andWhere(
            sprintf(
                '%s.%s = %s',
                $opportunityAlias,
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
     */
    protected function getOpportunityAlias(QueryBuilder $qb)
    {
        $fromParts = $qb->getDQLPart('from');
        /** @var $fromPart Expr\From */
        foreach ($fromParts as $fromPart) {
            if ($fromPart->getFrom() === Opportunity::class) {
                return $fromPart->getAlias();
            }
        }

        throw new DatasourceException('Couldn\'t find Opportunities alias in QueryBuilder.');
    }
}
