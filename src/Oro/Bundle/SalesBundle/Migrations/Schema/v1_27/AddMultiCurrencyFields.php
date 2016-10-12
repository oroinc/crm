<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_27;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\SqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtension;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;

use Oro\Bundle\CurrencyBundle\DependencyInjection\Configuration as CurrencyConfiguration;

class AddMultiCurrencyFields implements
    Migration,
    OrderedMigrationInterface,
    RenameExtensionAwareInterface
{
    /**
     * @var RenameExtension
     */
    protected $renameExtension;

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function setRenameExtension(RenameExtension $renameExtension)
    {
        $this->renameExtension =$renameExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queryBag)
    {
        self::addColumnsForMultiCurrency($schema, $queryBag, $this->renameExtension);
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queryBag
     * @param RenameExtension $renameExtension
     */
    public static function addColumnsForMultiCurrency(
        Schema $schema,
        QueryBag $queryBag,
        RenameExtension $renameExtension
    ) {
        $table = $schema->getTable('orocrm_sales_opportunity');

        //Rename columns for new type
        self::renameOpportunityFields($schema, $queryBag, $renameExtension);

        //Add columns for new type
        $table->addColumn(
            'budget_amount_currency',
            'currency',
            ['length' => 3, 'notnull' => false, 'comment' => '(DC2Type:currency)']
        );
        $table->addColumn(
            'close_revenue_currency',
            'currency',
            ['length' => 3, 'notnull' => false, 'comment' => '(DC2Type:currency)']
        );

        self::fillCurrencyFieldsWithDefaultValue($queryBag);
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     * @param RenameExtension $renameExtension
     */
    public static function renameOpportunityFields(
        Schema $schema,
        QueryBag $queries,
        RenameExtension $renameExtension
    ) {
        $table = $schema->getTable('orocrm_sales_opportunity');

        $renameExtension->renameColumn(
            $schema,
            $queries,
            $table,
            'budget_amount',
            'budget_amount_value'
        );

        $renameExtension->renameColumn(
            $schema,
            $queries,
            $table,
            'close_revenue',
            'close_revenue_value'
        );
    }

    /**
     * @param QueryBag $queries
     */
    public static function fillCurrencyFieldsWithDefaultValue(QueryBag $queries)
    {
         $queries->addPostQuery(
             new ParametrizedSqlMigrationQuery(
                 'UPDATE orocrm_sales_opportunity SET budget_amount_currency = :currency_code, 
                                 close_revenue_currency = :currency_code',
                 [
                     'currency_code' => CurrencyConfiguration::DEFAULT_CURRENCY
                 ],
                 [
                     'currency_code'     => Type::STRING,
                 ]
             )
         );
    }
}
