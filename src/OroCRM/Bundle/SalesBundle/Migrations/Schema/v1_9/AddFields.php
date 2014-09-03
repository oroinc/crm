<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_9;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;

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

    /**
     * @param Schema $schema
     */
    protected function modifyOrocrmSalesLeadTable(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_lead');

        $table->addColumn('customer_id', 'integer', ['notnull' => false]);
        $table->addIndex(['customer_id'], 'IDX_73DB46339395C3F3', []);

        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_b2bcustomer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * @param Schema $schema
     */
    protected function modifyOrocrmSalesOpportunityTable(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_opportunity');

        $table->addColumn('customer_id', 'integer', ['notnull' => false]);
        $table->addIndex(['customer_id'], 'IDX_C0FE4AAC9395C3F3', []);

        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_b2bcustomer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
