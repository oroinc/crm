<?php

namespace OroCRM\Bundle\CaseBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMCaseBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createCaseTable($schema);
        $this->createCaseItemTable($schema);
        $this->createCaseOriginTable($schema);
        $this->createCaseReportTable($schema);

        $this->createCaseForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function createCaseTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_case');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('workflow_item_id', 'integer', ['notnull' => false]);
        $table->addColumn('item_id', 'integer', ['notnull' => false]);
        $table->addColumn('workflow_step_id', 'integer', ['notnull' => false]);
        $table->addColumn('reporter_id', 'integer', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('subject', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('description', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('updatedAt', 'datetime', ['notnull' => false]);
        $table->addColumn('reportedOn', 'datetime', []);
        $table->addColumn('closedOn', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['workflow_item_id'], 'UNIQ_DB30FF11023C4EE');
        $table->addUniqueIndex(['reporter_id'], 'UNIQ_AB3BAC1EE1CFE6F5');
        $table->addUniqueIndex(['item_id'], 'UNIQ_AB3BAC1E126F525E');
        $table->addIndex(['owner_id'], 'IDX_DB30FF17E3C61F9', []);
        $table->addIndex(['workflow_step_id'], 'IDX_DB30FF171FE882C', []);
    }

    /**
     * @param Schema $schema
     */
    protected function createCaseItemTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_case_item');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('related_opportunity_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_cart_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_order_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_lead_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['related_order_id'], 'IDX_FF9EB5082B1C2395', []);
        $table->addIndex(['related_cart_id'], 'IDX_FF9EB50825CC071A', []);
        $table->addIndex(['related_lead_id'], 'IDX_FF9EB5083F4C8F28', []);
        $table->addIndex(['related_opportunity_id'], 'IDX_FF9EB508FA6C8510', []);

        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_opportunity'),
            ['related_opportunity_id'],
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
    }

    /**
     * @param Schema $schema
     */
    protected function createCaseOriginTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_case_origin');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('case_entity_id', 'integer', ['notnull' => false]);
        $table->addColumn('type', 'integer', []);
        $table->addColumn('value', 'string', ['notnull' => false, 'length' => 100]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['case_entity_id'], 'IDX_32669008AF060DA6', []);

        $table = $schema->getTable('orocrm_case_origin');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_case'),
            ['case_entity_id'],
            ['id'],
            [
                'onDelete' => null,
                'onUpdate' => null,
            ]
        );
    }

    /**
     * @param Schema $schema
     */
    protected function createCaseReportTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_case_reporter');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('customer_id', 'integer', ['notnull' => false]);
        $table->addColumn('user_id', 'integer', ['notnull' => false]);
        $table->addColumn('contact_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['user_id'], 'IDX_1849DE37A76ED395', []);
        $table->addIndex(['contact_id'], 'IDX_1849DE37E7A1254A', []);
        $table->addIndex(['customer_id'], 'IDX_1849DE379395C3F3', []);

        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_customer'),
            ['customer_id'],
            ['id'],
            [
                'onDelete' => 'SET NULL',
                'onUpdate' => null,
            ]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            ['contact_id'],
            ['id'],
            [
                'onDelete' => 'SET NULL',
                'onUpdate' => null,
            ]
        );
    }

    /**
     * @param Schema $schema
     */
    protected function createCaseForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_case');

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
            $schema->getTable('orocrm_case_item'),
            ['item_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
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
            $schema->getTable('orocrm_case_reporter'),
            ['reporter_id'],
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
    }
}
