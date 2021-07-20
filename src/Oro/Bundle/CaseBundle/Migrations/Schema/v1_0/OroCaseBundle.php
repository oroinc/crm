<?php

namespace Oro\Bundle\CaseBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCaseBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createCaseTable($schema);
        $this->createCaseSourceTable($schema);
        $this->createCaseSourceTranslationTable($schema);
        $this->createCaseStatusTable($schema);
        $this->createCaseStatusTranslationTable($schema);
        $this->createCasePriorityTable($schema);
        $this->createCasePriorityTranslationTable($schema);

        $this->createCaseForeignKeys($schema);
    }

    protected function createCaseTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_case');
        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('subject', 'string', array('length' => 255));
        $table->addColumn('description', 'text', array('notnull' => false));
        $table->addColumn('resolution', 'text', array('notnull' => false));
        $table->addColumn('related_contact_id', 'integer', array('notnull' => false));
        $table->addColumn('related_account_id', 'integer', array('notnull' => false));
        $table->addColumn('assigned_to_id', 'integer', array('notnull' => false));
        $table->addColumn('owner_id', 'integer', array('notnull' => false));
        $table->addColumn('source_name', 'string', array('notnull' => false, 'length' => 16));
        $table->addColumn('status_name', 'string', array('notnull' => false, 'length' => 16));
        $table->addColumn('priority_name', 'string', array('notnull' => false, 'length' => 16));
        $table->addColumn('createdAt', 'datetime', array());
        $table->addColumn('updatedAt', 'datetime', array('notnull' => false));
        $table->addColumn('reportedAt', 'datetime', array());
        $table->addColumn('closedAt', 'datetime', array('notnull' => false));

        $table->setPrimaryKey(array('id'));
        $table->addIndex(array('owner_id'), 'IDX_AB3BAC1E7E3C61F9', array());
        $table->addIndex(array('assigned_to_id'), 'IDX_AB3BAC1EF4BD7827', array());
        $table->addIndex(array('related_contact_id'), 'IDX_AB3BAC1E6D6C2DFA', array());
        $table->addIndex(array('related_account_id'), 'IDX_AB3BAC1E11A6570A', array());
        $table->addIndex(array('source_name'), 'IDX_AB3BAC1E5FA9FB05', array());
        $table->addIndex(array('priority_name'), 'IDX_AB3BAC1E965BD3DF', array());
    }

    protected function createCaseSourceTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_case_source');
        $table->addColumn('name', 'string', array('length' => 16));
        $table->addColumn('label', 'string', array('length' => 255));
        $table->setPrimaryKey(array('name'));
    }

    /**
     * Generate table orocrm_case_source_trans
     */
    public static function createCaseSourceTranslationTable(Schema $schema)
    {
        /** Generate table orocrm_case_source_trans **/
        $table = $schema->createTable('orocrm_case_source_trans');
        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('foreign_key', 'string', array('length' => 16));
        $table->addColumn('content', 'string', array('length' => 255));
        $table->addColumn('locale', 'string', array('length' => 8));
        $table->addColumn('object_class', 'string', array('length' => 255));
        $table->addColumn('field', 'string', array('length' => 32));
        $table->setPrimaryKey(array('id'));
        $table->addIndex(
            array('locale', 'object_class', 'field', 'foreign_key'),
            'case_source_translation_idx',
            array()
        );
        /** End of generate table orocrm_case_source_trans **/
    }

    protected function createCaseStatusTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_case_status');
        $table->addColumn('name', 'string', array('length' => 16));
        $table->addColumn('`order`', 'integer');
        $table->addColumn('label', 'string', array('length' => 255));
        $table->setPrimaryKey(array('name'));
    }

    /**
     * Generate table orocrm_case_status_trans
     */
    public static function createCaseStatusTranslationTable(Schema $schema)
    {
        /** Generate table orocrm_case_status_trans **/
        $table = $schema->createTable('orocrm_case_status_trans');
        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('foreign_key', 'string', array('length' => 16));
        $table->addColumn('content', 'string', array('length' => 255));
        $table->addColumn('locale', 'string', array('length' => 8));
        $table->addColumn('object_class', 'string', array('length' => 255));
        $table->addColumn('field', 'string', array('length' => 32));
        $table->setPrimaryKey(array('id'));
        $table->addIndex(
            array('locale', 'object_class', 'field', 'foreign_key'),
            'case_status_translation_idx',
            array()
        );
        /** End of generate table orocrm_case_status_trans **/
    }

    protected function createCasePriorityTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_case_priority');
        $table->addColumn('name', 'string', array('length' => 16));
        $table->addColumn('`order`', 'integer');
        $table->addColumn('label', 'string', array('length' => 255));
        $table->setPrimaryKey(array('name'));
    }

    /**
     * Generate table orocrm_case_priority_trans
     */
    public static function createCasePriorityTranslationTable(Schema $schema)
    {
        /** Generate table orocrm_case_priority_trans **/
        $table = $schema->createTable('orocrm_case_priority_trans');
        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('foreign_key', 'string', array('length' => 16));
        $table->addColumn('content', 'string', array('length' => 255));
        $table->addColumn('locale', 'string', array('length' => 8));
        $table->addColumn('object_class', 'string', array('length' => 255));
        $table->addColumn('field', 'string', array('length' => 32));
        $table->setPrimaryKey(array('id'));
        $table->addIndex(
            array('locale', 'object_class', 'field', 'foreign_key'),
            'case_priority_translation_idx',
            array()
        );
        /** End of generate table orocrm_case_priority_trans **/
    }

    protected function createCaseForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_case');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            array('related_contact_id'),
            array('id'),
            array('onDelete' => 'SET NULL', 'onUpdate' => null)
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_account'),
            array('related_account_id'),
            array('id'),
            array('onDelete' => 'SET NULL', 'onUpdate' => null)
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_case_source'),
            array('source_name'),
            array('name'),
            array('onDelete' => 'SET NULL', 'onUpdate' => null)
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_case_status'),
            array('status_name'),
            array('name'),
            array('onDelete' => 'SET NULL', 'onUpdate' => null)
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_case_priority'),
            array('priority_name'),
            array('name'),
            array('onDelete' => 'SET NULL', 'onUpdate' => null)
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            array('assigned_to_id'),
            array('id'),
            array('onDelete' => 'SET NULL', 'onUpdate' => null)
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            array('owner_id'),
            array('id'),
            array('onDelete' => 'SET NULL', 'onUpdate' => null)
        );
    }
}
