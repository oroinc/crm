<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_24;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class CreateB2bCustomerPhone implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOrocrmB2bCustomerEmailTable($schema);
        /** Foreign keys generation **/
        $this->addOrocrmB2bCustomerEmailForeignKeys($schema);
    }
    
    /**
     * Create orocrm_b2bcustomer_email table
     *
     * @param Schema $schema
     */
    protected function createOrocrmB2bCustomerEmailTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_b2bcustomer_email');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('email', 'string', ['length' => 255]);
        $table->addColumn('is_primary', 'boolean', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'IDX_335A28C37E3C61F9', []);
        $table->addIndex(['email', 'is_primary'], 'primary_email_idx', []);
    }

    /**
     * Add orocrm_b2bcustomer_email foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmB2bCustomerEmailForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_b2bcustomer_email');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_b2bcustomer'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}