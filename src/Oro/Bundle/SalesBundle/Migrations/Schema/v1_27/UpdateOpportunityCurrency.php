<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_27;

use Doctrine\DBAL\Portability\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\CurrencyBundle\DependencyInjection\Configuration;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class UpdateOpportunityCurrency implements
    Migration,
    ContainerAwareInterface,
    OrderedMigrationInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 2;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** @var Connection $connection */
        $connection = $this->container->get('doctrine')->getConnection();
        $currencies = $connection->fetchAll('
                                              SELECT 
                                                oro_organization.id as organization_id,
                                                oro_config_value.text_value
                                              FROM
                                                oro_config
                                              INNER JOIN
                                                oro_organization
                                              ON 
                                                oro_organization.id = oro_config.record_id
                                              INNER JOIN
                                                oro_config_value
                                              ON 
                                                oro_config.id = oro_config_value.config_id
                                              WHERE
                                                oro_config_value.name = \'default_currency\'
         ');

        $currencies = array_column($currencies, 'text_value', 'organization_id');

        $defaultCurrency = $connection->fetchColumn('
                                              SELECT 
                                                oro_config_value.text_value 
                                              FROM 
                                                oro_config_value 
                                              INNER JOIN 
                                                oro_config 
                                              ON 
                                                oro_config_value.config_id = oro_config.id
                                              WHERE 
                                                oro_config.entity = ?
                                              AND 
                                                oro_config_value.name = \'default_currency\'
                                              ', ['app'], 0);

        if (!$defaultCurrency) {
            $defaultCurrency = Configuration::DEFAULT_CURRENCY;
        }

        $this->updateOpportunityTable($queries, $currencies, $defaultCurrency);
    }

    /**
     * @param QueryBag      $queries
     * @param array         $currencies
     * @param string        $defaultCurrency
     */
    protected function updateOpportunityTable(QueryBag $queries, array $currencies, $defaultCurrency)
    {
        $query = 'UPDATE orocrm_sales_opportunity 
                  SET budget_amount_currency = :currency, close_revenue_currency = :currency';

        if (!empty($currencies)) {
            $query .= sprintf(' WHERE organization_id 
                          NOT IN (%s)', implode(',', array_keys($currencies)));
        }
        $migrationQuery = new ParametrizedSqlMigrationQuery();

        $migrationQuery->addSql(
            $query,
            ['currency' => $defaultCurrency],
            ['currency' => Types::STRING]
        );

        $queries->addPostQuery($migrationQuery);

        foreach ($currencies as $id => $currency) {
            $query = 'UPDATE orocrm_sales_opportunity 
                        SET budget_amount_currency = :currency, close_revenue_currency = :currency 
                        WHERE organization_id = :organization_id';

            $migrationQuery = new ParametrizedSqlMigrationQuery();

            $migrationQuery->addSql(
                $query,
                ['currency' => $currency, 'organization_id' => $id],
                ['currency' => Types::STRING, 'organization_id' => Types::INTEGER]
            );
            $queries->addPostQuery($migrationQuery);
        }
    }
}
