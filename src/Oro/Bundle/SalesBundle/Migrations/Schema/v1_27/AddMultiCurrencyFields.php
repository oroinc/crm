<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_27;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\CurrencyBundle\DependencyInjection\Configuration as CurrencyConfiguration;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddMultiCurrencyFields implements
    Migration,
    OrderedMigrationInterface,
    RenameExtensionAwareInterface
{
    use RenameExtensionAwareTrait;

    #[\Override]
    public function getOrder(): int
    {
        return 1;
    }

    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $table = $schema->getTable('orocrm_sales_opportunity');

        $this->renameOpportunityFields($schema, $queries);

        //Add columns for new type
        $table->addColumn('budget_amount_currency', 'currency', [
            'length' => 3,
            'notnull' => false,
            'comment' => '(DC2Type:currency)'
        ]);
        $table->addColumn('close_revenue_currency', 'currency', [
            'length' => 3,
            'notnull' => false,
            'comment' => '(DC2Type:currency)'
        ]);

        $this->fillCurrencyFieldsWithDefaultValue($queries);
    }

    private function renameOpportunityFields(Schema $schema, QueryBag $queries): void
    {
        $table = $schema->getTable('orocrm_sales_opportunity');

        /**
         * Fix issue with incorrect field type instead DECIMAL instead of NUMERIC
         */
        $type = Type::getType('money_value');
        $table->modifyColumn(
            'budget_amount',
            ['type' => $type, 'notnull' => false, 'comment' => '(DC2Type:money_value)']
        );

        $table->modifyColumn(
            'close_revenue',
            ['type' => $type, 'notnull' => false, 'comment' => '(DC2Type:money_value)']
        );

        $this->renameExtension->renameColumn($schema, $queries, $table, 'budget_amount', 'budget_amount_value');
        $this->renameExtension->renameColumn($schema, $queries, $table, 'close_revenue', 'close_revenue_value');
    }

    private function fillCurrencyFieldsWithDefaultValue(QueryBag $queries): void
    {
        $queries->addPostQuery(
            new ParametrizedSqlMigrationQuery(
                'UPDATE orocrm_sales_opportunity SET budget_amount_currency = :currency_code, 
                               close_revenue_currency = :currency_code',
                [
                    'currency_code' => CurrencyConfiguration::DEFAULT_CURRENCY
                ],
                [
                    'currency_code' => Types::STRING,
                ]
            )
        );
    }
}
