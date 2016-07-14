<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_24;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class CreteLeadEmail implements Migration, OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 4;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOrocrmSalesLeadEmailTable($schema);
        /** Foreign keys generation **/
        $this->addOrocrmSalesLeadEmailForeignKeys($schema);

        $queries->addPostQuery(
            'INSERT INTO orocrm_sales_lead_email (owner_id, email, is_primary)
            SELECT orocrm_sales_lead.id, orocrm_sales_lead.email, \'1\' FROM orocrm_sales_lead WHERE email IS NOT NULL'
        );
    }

    /**
     * Create orocrm_sales_lead_email table
     *
     * @param Schema $schema
     */
    protected function createOrocrmSalesLeadEmailTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_sales_lead_email');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('email', 'string', ['length' => 255]);
        $table->addColumn('is_primary', 'boolean', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'IDX_9F15A0937E3C61F9', []);
        $table->addIndex(['email', 'is_primary'], 'lead_primary_email_idx', []);
    }

    /**
     * Add orocrm_sales_lead_email foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmSalesLeadEmailForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_lead_email');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_lead'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
