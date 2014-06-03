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
        $this->createCaseOriginTable($schema);
        $this->createCaseOriginTranslationTable($schema);

        $this->createCaseForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function createCaseTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_case');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('reporter_customer_id', 'integer', ['notnull' => false]);
        $table->addColumn('workflow_item_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_cart_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_order_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_lead_id', 'integer', ['notnull' => false]);
        $table->addColumn('reporter_contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('workflow_step_id', 'integer', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('origin_name', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('reporter_user_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_opportunity_id', 'integer', ['notnull' => false]);
        $table->addColumn('subject', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('description', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('updatedAt', 'datetime', ['notnull' => false]);
        $table->addColumn('reportedAt', 'datetime', []);
        $table->addColumn('closedAt', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['workflow_item_id'], 'UNIQ_AB3BAC1E1023C4EE');
        $table->addIndex(['owner_id'], 'IDX_AB3BAC1E7E3C61F9', []);
        $table->addIndex(['origin_name'], 'IDX_AB3BAC1EB03BC868', []);
        $table->addIndex(['workflow_step_id'], 'IDX_AB3BAC1E71FE882C', []);
        $table->addIndex(['related_order_id'], 'IDX_AB3BAC1E2B1C2395', []);
        $table->addIndex(['related_cart_id'], 'IDX_AB3BAC1E25CC071A', []);
        $table->addIndex(['related_lead_id'], 'IDX_AB3BAC1E3F4C8F28', []);
        $table->addIndex(['related_opportunity_id'], 'IDX_AB3BAC1EFA6C8510', []);
        $table->addIndex(['reporter_user_id'], 'IDX_AB3BAC1EDF3D6D95', []);
        $table->addIndex(['reporter_contact_id'], 'IDX_AB3BAC1E6F85A64A', []);
        $table->addIndex(['reporter_customer_id'], 'IDX_AB3BAC1E931DE770', []);
    }

    /**
     * @param Schema $schema
     */
    protected function createCaseOriginTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_case_origin');
        $table->addColumn('name', 'string', ['length' => 16]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->setPrimaryKey(['name']);
    }

    /**
     * Generate table orocrm_case_origin_translation
     *
     * @param Schema $schema
     */
    public static function createCaseOriginTranslationTable(Schema $schema)
    {
        /** Generate table orocrm_case_origin_translation **/
        $table = $schema->createTable('orocrm_case_origin_translation');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('foreign_key', 'string', ['length' => 16]);
        $table->addColumn('content', 'string', ['length' => 255]);
        $table->addColumn('locale', 'string', ['length' => 8]);
        $table->addColumn('object_class', 'string', ['length' => 255]);
        $table->addColumn('field', 'string', ['length' => 32]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['locale', 'object_class', 'field', 'foreign_key'], 'case_origin_translation_idx', []);
        /** End of generate table orocrm_case_origin_translation **/
    }

    /**
     * @param Schema $schema
     */
    protected function createCaseForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_case');
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
            $schema->getTable('orocrm_case_origin'),
            ['origin_name'],
            ['name'],
            [
                'onDelete' => 'SET NULL',
                'onUpdate' => null,
            ]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['reporter_user_id'],
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
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
