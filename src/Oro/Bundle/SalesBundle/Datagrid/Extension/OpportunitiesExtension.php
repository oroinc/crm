<?php

namespace Oro\Bundle\SalesBundle\Datagrid\Extension;

use Doctrine\ORM\Query\Expr;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

class OpportunitiesExtension extends AbstractExtension
{
    /**
     * @var ConfigProvider
     */
    protected $salesProvider;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->salesProvider = $configManager->getProvider('sales');
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        return
            $this->parameters->has('customer_class') &&
            $this->parameters->get('customer_id') &&
            $config->getDatasourceType() === OrmDatasource::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        /** @var $datasource OrmDataSource */
        $customerClass   = $this->parameters->get('customer_class');
        $customersConfig = $this->salesProvider->getConfig(Opportunity::class)->get('customers');
        $customerField   = $customersConfig[$customerClass]['association_name'];
        $queryBuilder    = $datasource->getQueryBuilder();
        $customerIdParam = sprintf(':customerIdParam_%s', $customerField);
        $queryBuilder->andWhere(
            sprintf(
                '%s.%s = %s',
                $this->getOpportunityAlias($queryBuilder),
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

        throw new \LogicException('Couldn\'t find Opportunities alias in QueryBuilder.');
    }
}
