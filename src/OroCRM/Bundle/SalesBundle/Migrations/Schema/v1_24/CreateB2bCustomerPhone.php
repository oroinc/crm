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
        $this->createOrocrmB2bCustomerPhoneTable($schema);
        /** Foreign keys generation **/
        $this->addOrocrmB2bCustomerPhoneForeignKeys($schema);
    }
    /**
     * Create orocrm_lead_phone table
     *
     * @param Schema $schema
     */
    protected function createOrocrmB2bCustomerPhoneTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_b2bcustomer_phone');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('phone', 'string', ['length' => 255]);
        $table->addColumn('is_primary', 'boolean', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'IDX_8475907F7E3C61F9', []);
        $table->addIndex(['phone', 'is_primary'], 'primary_phone_idx', []);
        $table->addIndex(['phone'], 'phone_idx');
    }
    
    /**
     * Add orocrm_b2bcustomer_phone foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmB2bCustomerPhoneForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_b2bcustomer_phone');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_b2bcustomer'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}