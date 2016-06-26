<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_24;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class CreteLeadPhone implements Migration, OrderedMigrationInterface
{
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
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOrocrmLeadPhoneTable($schema);

        /** Foreign keys generation **/
        $this->addOrocrmLeadPhoneForeignKeys($schema);
    }

    /**
     * Create orocrm_lead_phone table
     *
     * @param Schema $schema
     */
    protected function createOrocrmLeadPhoneTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_lead_phone');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('phone', 'string', ['length' => 255]);
        $table->addColumn('is_primary', 'boolean', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'IDX_9087C36A7E3C61F9', []);
        $table->addIndex(['phone', 'is_primary'], 'primary_phone_idx', []);
        $table->addIndex(['phone'], 'phone_idx');
    }

    /**
     * Add orocrm_lead_phone foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmLeadPhoneForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_lead_phone');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_lead'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
