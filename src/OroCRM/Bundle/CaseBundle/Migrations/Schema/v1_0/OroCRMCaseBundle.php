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

        $this->createCaseForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function createCaseTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_case');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('related_customer_id', 'integer', ['notnull' => false]);
        $table->addColumn('workflow_item_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_order_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('workflow_step_id', 'integer', ['notnull' => false]);
        $table->addColumn('reporter_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_opportunity_id', 'integer', ['notnull' => false]);
        $table->addColumn('origin_code', 'string', ['notnull' => false, 'length' => 30]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_cart_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_lead_id', 'integer', ['notnull' => false]);
        $table->addColumn('subject', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('description', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('updatedAt', 'datetime', array('notnull' => false));
        $table->addColumn('reportedAt', 'datetime', array());
        $table->addColumn('closedAt', 'datetime', array('notnull' => false));
        $table->setPrimaryKey(array('id'));
        $table->addUniqueIndex(array('workflow_item_id'), 'UNIQ_AB3BAC1E1023C4EE');
        $table->addIndex(array('owner_id'), 'IDX_AB3BAC1E7E3C61F9', array());
        $table->addIndex(array('origin_code'), 'IDX_AB3BAC1EB03BC868', array());
        $table->addIndex(array('workflow_step_id'), 'IDX_AB3BAC1E71FE882C', array());
        $table->addIndex(array('related_order_id'), 'IDX_AB3BAC1E2B1C2395', array());
        $table->addIndex(array('related_cart_id'), 'IDX_AB3BAC1E25CC071A', array());
        $table->addIndex(array('related_lead_id'), 'IDX_AB3BAC1E3F4C8F28', array());
        $table->addIndex(array('related_opportunity_id'), 'IDX_AB3BAC1EFA6C8510', array());
        $table->addIndex(array('reporter_id'), 'IDX_AB3BAC1EE1CFE6F5', array());
        $table->addIndex(array('related_contact_id'), 'IDX_AB3BAC1E6D6C2DFA', array());
        $table->addIndex(array('related_customer_id'), 'IDX_AB3BAC1E587EBD77', array());
    }

    /**
     * @param Schema $schema
     */
    protected function createCaseOriginTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_case_origin');
        $table->addColumn('code', 'string', array('length' => 30));
        $table->addColumn('label', 'string', array('notnull' => false, 'length' => 100));
        $table->setPrimaryKey(array('code'));
    }

    /**
     * @param Schema $schema
     */
    protected function createCaseForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_case');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_customer'),
            array('related_customer_id'),
            array('id'),
            array('onDelete' => 'SET NULL', 'onUpdate' => null)
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_item'),
            array('workflow_item_id'),
            array('id'),
            array('onDelete' => 'SET NULL', 'onUpdate' => null)
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_order'),
            array('related_order_id'),
            array('id'),
            array('onDelete' => 'SET NULL', 'onUpdate' => null)
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            array('related_contact_id'),
            array('id'),
            array('onDelete' => 'SET NULL', 'onUpdate' => null)
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_step'),
            array('workflow_step_id'),
            array('id'),
            array('onDelete' => 'SET NULL', 'onUpdate' => null)
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            array('reporter_id'),
            array('id'),
            array('onDelete' => 'SET NULL', 'onUpdate' => null)
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_opportunity'),
            array('related_opportunity_id'),
            array('id'),
            array('onDelete' => 'SET NULL', 'onUpdate' => null)
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_case_origin'),
            array('origin_code'),
            array('code'),
            array('onDelete' => 'SET NULL', 'onUpdate' => null)
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            array('owner_id'),
            array('id'),
            array('onDelete' => 'SET NULL', 'onUpdate' => null)
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_cart'),
            array('related_cart_id'),
            array('id'),
            array('onDelete' => 'SET NULL', 'onUpdate' => null)
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_lead'),
            array('related_lead_id'),
            array('id'),
            array('onDelete' => 'SET NULL', 'onUpdate' => null)
        );
    }
}
