<?php

namespace OroCRM\Bundle\CaseBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Schema\Table;

class OroCRMCaseBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $this->createTable($schema);
        $this->createKeys($schema, $table);
    }

    /**
     * @param Schema $schema
     * @return Table
     */
    protected function createTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_case');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('workflow_item_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_cart_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_order_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_lead_id', 'integer', ['notnull' => false]);
        $table->addColumn('reporter_contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('workflow_step_id', 'integer', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('reporter_customer_id', 'integer', ['notnull' => false]);
        $table->addColumn('reporter_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_opportunity_id', 'integer', ['notnull' => false]);
        $table->addColumn('subject', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('description', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('updatedAt', 'datetime', ['notnull' => false]);
        $table->addColumn('reportedOn', 'datetime', []);
        $table->addColumn('closedOn', 'datetime', ['notnull' => false]);
        $table->addColumn('email_address', 'string', ['notnull' => false, 'length' => 100]);
        $table->addColumn('phone', 'string', ['notnull' => false, 'length' => 100]);
        $table->addColumn('web', 'string', ['notnull' => false, 'length' => 100]);
        $table->addColumn('other_contact', 'string', ['notnull' => false, 'length' => 100]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['workflow_item_id'], 'UNIQ_DB30FF11023C4EE');
        $table->addIndex(['owner_id'], 'IDX_DB30FF17E3C61F9', []);
        $table->addIndex(['reporter_id'], 'IDX_DB30FF1E1CFE6F5', []);
        $table->addIndex(['reporter_contact_id'], 'IDX_DB30FF16F85A64A', []);
        $table->addIndex(['reporter_customer_id'], 'IDX_DB30FF1931DE770', []);
        $table->addIndex(['related_order_id'], 'IDX_DB30FF12B1C2395', []);
        $table->addIndex(['related_cart_id'], 'IDX_DB30FF125CC071A', []);
        $table->addIndex(['related_lead_id'], 'IDX_DB30FF13F4C8F28', []);
        $table->addIndex(['related_opportunity_id'], 'IDX_DB30FF1FA6C8510', []);
        $table->addIndex(['workflow_step_id'], 'IDX_DB30FF171FE882C', []);
        return $table;
    }

    /**
     * @param Schema $schema
     * @param Table $table
     */
    public function createKeys(Schema $schema, Table $table)
    {
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_item'),
            ['workflow_item_id'],
            ['id'],
            [
                'onDelete' => 'SET NULL',
                'onUpdate' => null,
            ]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_cart'),
            ['related_cart_id'],
            ['id'],
            [
                'onDelete' => 'SET NULL',
                'onUpdate' => null,
            ]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_order'),
            ['related_order_id'],
            ['id'],
            [
                'onDelete' => 'SET NULL',
                'onUpdate' => null,
            ]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_lead'),
            ['related_lead_id'],
            ['id'],
            [
                'onDelete' => 'SET NULL',
                'onUpdate' => null,
            ]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            ['reporter_contact_id'],
            ['id'],
            [
                'onDelete' => 'SET NULL',
                'onUpdate' => null,
            ]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_step'),
            ['workflow_step_id'],
            ['id'],
            [
                'onDelete' => 'SET NULL',
                'onUpdate' => null,
            ]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_customer'),
            ['reporter_customer_id'],
            ['id'],
            [
                'onDelete' => 'SET NULL',
                'onUpdate' => null,
            ]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['reporter_id'],
            ['id'],
            [
                'onDelete' => 'SET NULL',
                'onUpdate' => null,
            ]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_opportunity'),
            ['related_opportunity_id'],
            ['id'],
            [
                'onDelete' => 'SET NULL',
                'onUpdate' => null,
            ]
        );
    }
}
