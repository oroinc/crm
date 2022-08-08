<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_10;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddFields implements Migration, OrderedMigrationInterface
{
    /**
     * @inheritdoc
     */
    public function getOrder()
    {
        return 20;
    }

    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->modifyOrocrmSalesLeadTable($schema);
        $this->modifyOrocrmSalesOpportunityTable($schema);

        $queries->addPostQuery(new MigrateAccountRelations());
    }

    protected function modifyOrocrmSalesLeadTable(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_lead');

        $table->addColumn('customer_id', 'integer', ['notnull' => false]);
        $table->addIndex(['customer_id'], 'IDX_73DB46339395C3F3', []);
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
        $table->addIndex(['data_channel_id'], 'IDX_73DB4633BDC09B73', []);

        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_b2bcustomer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_73DB4633BDC09B73'
        );
    }

    protected function modifyOrocrmSalesOpportunityTable(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_opportunity');

        $table->addColumn('customer_id', 'integer', ['notnull' => false]);
        $table->addIndex(['customer_id'], 'IDX_C0FE4AAC9395C3F3', []);
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
        $table->addIndex(['data_channel_id'], 'IDX_C0FE4AACBDC09B73', []);

        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_b2bcustomer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_C0FE4AACBDC09B73'
        );
    }
}
