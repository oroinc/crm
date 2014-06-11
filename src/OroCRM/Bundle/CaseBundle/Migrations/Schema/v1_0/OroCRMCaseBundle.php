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
        $this->createCaseStatusTable($schema);
        $this->createCaseStatusTranslationTable($schema);

        $this->createCaseForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function createCaseTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_case');
        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('subject', 'string', array('length' => 255));
        $table->addColumn('description', 'text', array('notnull' => false));
        $table->addColumn('related_contact_id', 'integer', array('notnull' => false));
        $table->addColumn('related_account_id', 'integer', array('notnull' => false));
        $table->addColumn('assigned_to_id', 'integer', array('notnull' => false));
        $table->addColumn('owner_id', 'integer', array('notnull' => false));
        $table->addColumn('origin_name', 'string', array('notnull' => false, 'length' => 16));
        $table->addColumn('status_name', 'string', array('notnull' => false, 'length' => 16));
        $table->addColumn('createdAt', 'datetime', array());
        $table->addColumn('updatedAt', 'datetime', array('notnull' => false));
        $table->addColumn('reportedAt', 'datetime', array());
        $table->addColumn('closedAt', 'datetime', array('notnull' => false));

        $table->setPrimaryKey(array('id'));
        $table->addIndex(array('owner_id'), 'IDX_AB3BAC1E7E3C61F9', array());
        $table->addIndex(array('assigned_to_id'), 'IDX_AB3BAC1EF4BD7827', array());
        $table->addIndex(array('related_contact_id'), 'IDX_AB3BAC1E6D6C2DFA', array());
        $table->addIndex(array('related_account_id'), 'IDX_AB3BAC1E11A6570A', array());
        $table->addIndex(array('origin_name'), 'IDX_AB3BAC1EB03BC868', array());
        $table->addIndex(array('status_name'), 'IDX_AB3BAC1E6625D392', array());
    }

    /**
     * @param Schema $schema
     */
    protected function createCaseOriginTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_case_origin');
        $table->addColumn('name', 'string', array('length' => 16));
        $table->addColumn('label', 'string', array('length' => 255));
        $table->setPrimaryKey(array('name'));
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
        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('foreign_key', 'string', array('length' => 16));
        $table->addColumn('content', 'string', array('length' => 255));
        $table->addColumn('locale', 'string', array('length' => 8));
        $table->addColumn('object_class', 'string', array('length' => 255));
        $table->addColumn('field', 'string', array('length' => 32));
        $table->setPrimaryKey(array('id'));
        $table->addIndex(
            array('locale', 'object_class', 'field', 'foreign_key'),
            'case_origin_translation_idx',
            array()
        );
        /** End of generate table orocrm_case_origin_translation **/
    }

    /**
     * @param Schema $schema
     */
    protected function createCaseStatusTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_case_status');
        $table->addColumn('name', 'string', array('length' => 16));
        $table->addColumn('sort_order', 'integer');
        $table->addColumn('label', 'string', array('length' => 255));
        $table->setPrimaryKey(array('name'));
    }

    /**
     * Generate table orocrm_case_status_translation
     *
     * @param Schema $schema
     */
    public static function createCaseStatusTranslationTable(Schema $schema)
    {
        /** Generate table orocrm_case_status_translation **/
        $table = $schema->createTable('orocrm_case_status_translation');
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
        /** End of generate table orocrm_case_status_translation **/
    }

    /**
     * @param Schema $schema
     */
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
            $schema->getTable('orocrm_case_origin'),
            array('origin_name'),
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
