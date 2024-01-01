<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_29;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddCustomersTable implements Migration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        $this->addCustomerTable($schema);
        $this->updateOpportunityTable($schema);
        $this->updateLeadTable($schema);
    }

    private function addCustomerTable(Schema $schema): void
    {
        $table = $schema->createTable('orocrm_sales_customer');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('account_id', 'integer', ['notnull' => true]);
        $table->setPrimaryKey(['id']);

        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_account'),
            ['account_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    private function updateOpportunityTable(Schema $schema): void
    {
        $table = $schema->getTable('orocrm_sales_opportunity');
        $table->addColumn('customer_association_id', 'integer', ['notnull' => false]);
        $table->addIndex(['customer_association_id'], 'IDX_C0FE4AAC76D4FC6F');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_customer'),
            ['customer_association_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    private function updateLeadTable(Schema $schema): void
    {
        $table = $schema->getTable('orocrm_sales_lead');
        $table->addColumn('customer_association_id', 'integer', ['notnull' => false]);
        $table->addIndex(['customer_association_id'], 'IDX_73DB463376D4FC6F');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_customer'),
            ['customer_association_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
