<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_27;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\CurrencyBundle\DependencyInjection\Configuration as CurrencyConfiguration;
use Oro\Bundle\MigrationBundle\Migration\Extension\DatabasePlatformAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtension;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddMultiCurrencyFields implements
    Migration,
    OrderedMigrationInterface,
    RenameExtensionAwareInterface,
    DatabasePlatformAwareInterface
{
    /** @var AbstractPlatform */
    protected $platform;

    public function setDatabasePlatform(AbstractPlatform $platform)
    {
        $this->platform = $platform;
    }

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
        $this->renameExtension = $renameExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queryBag)
    {
        self::addColumnsForMultiCurrency($schema, $queryBag, $this->renameExtension, $this->platform);
    }

    public static function addColumnsForMultiCurrency(
        Schema $schema,
        QueryBag $queryBag,
        RenameExtension $renameExtension,
        AbstractPlatform $platform
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

    public static function renameOpportunityFields(
        Schema $schema,
        QueryBag $queries,
        RenameExtension $renameExtension
    ) {
        $table = $schema->getTable('orocrm_sales_opportunity');

        /**
         * Fix issue with incorrect field type instead DECIMAL instead of NUMERIC
         */
        $type = Type::getType('money_value');
        $table->changeColumn(
            'budget_amount',
            ['type' => $type, 'notnull' => false, 'comment' => '(DC2Type:money_value)']
        );

        $table->changeColumn(
            'close_revenue',
            ['type' => $type, 'notnull' => false, 'comment' => '(DC2Type:money_value)']
        );

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
                    'currency_code' => Types::STRING,
                ]
            )
        );
    }
}
