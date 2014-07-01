<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_6;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMSalesBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::addFieldToOrocrmSalesLead($schema);
        self::addFieldToOrocrmSalesOpportunity($schema);
        self::addFieldToOrocrmSalesFunnel($schema);

        self::addForeignKeyToOrocrmSalesLead($schema);
        self::addForeignKeyToOrocrmSalesOpportunity($schema);
        self::addForeignKeyToOrocrmSalesFunnel($schema);
    }

    /**
     * @param Schema $schema
     */
    public static function addFieldToOrocrmSalesLead(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_lead');
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
        $table->addIndex(['data_channel_id'], 'IDX_73DB463372F5A1AA', []);
    }

    /**
     * @param Schema $schema
     */
    public static function addFieldToOrocrmSalesOpportunity(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_opportunity');
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
        $table->addIndex(['data_channel_id'], 'IDX_C0FE4AAC72F5A1AA', []);
    }

    /**
     * @param Schema $schema
     */
    public static function addFieldToOrocrmSalesFunnel(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_funnel');
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
        $table->addIndex(['data_channel_id'], 'IDX_E20C734472F5A1AA', []);
    }

    /**
     * @param Schema $schema
     */
    public static function addForeignKeyToOrocrmSalesLead(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_lead');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_73DB463372F5A1AA'
        );
    }

    /**
     * @param Schema $schema
     */
    public static function addForeignKeyToOrocrmSalesOpportunity(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_opportunity');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_C0FE4AAC72F5A1AA'
        );
    }

    /**
     * @param Schema $schema
     */
    public static function addForeignKeyToOrocrmSalesFunnel(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_funnel');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_E20C734472F5A1AA'
        );
    }
}
