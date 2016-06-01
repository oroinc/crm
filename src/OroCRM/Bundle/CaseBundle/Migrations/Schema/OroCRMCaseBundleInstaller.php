<?php

namespace OroCRM\Bundle\CaseBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtension;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareInterface;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtension;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtension;
use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtensionAwareInterface;

use OroCRM\Bundle\CaseBundle\Migrations\Schema\v1_7\InheritanceActivityTargets;
use OroCRM\Bundle\CaseBundle\Migrations\Schema\v1_8\CreateActivityAssociation;

class OroCRMCaseBundleInstaller implements
    Installation,
    AttachmentExtensionAwareInterface,
    ActivityExtensionAwareInterface,
    ActivityListExtensionAwareInterface,
    NoteExtensionAwareInterface
{
    /** @var AttachmentExtension */
    protected $attachmentExtension;

    /** @var ActivityExtension */
    protected $activityExtension;

    /** @var NoteExtension */
    protected $noteExtension;

    /** @var ActivityListExtension */
    protected $activityListExtension;

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_8';
    }

    /**
     * {@inheritdoc}
     */
    public function setActivityListExtension(ActivityListExtension $activityListExtension)
    {
        $this->activityListExtension = $activityListExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttachmentExtension(AttachmentExtension $attachmentExtension)
    {
        $this->attachmentExtension = $attachmentExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function setNoteExtension(NoteExtension $noteExtension)
    {
        $this->noteExtension = $noteExtension;
    }


    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOrocrmCaseTable($schema);
        $this->createOrocrmCaseSourceTable($schema);
        $this->createOrocrmCaseSourceTranslationTable($schema);
        $this->createOrocrmCaseStatusTable($schema);
        $this->createOrocrmCaseStatusTranslationTable($schema);
        $this->createOrocrmCasePriorityTable($schema);
        $this->createOrocrmCasePriorityTranslationTable($schema);
        $this->createOrocrmCaseCommentTranslationTable($schema);

        /** Tables update */
        $this->addOroEmailMailboxProcessSettingsColumns($schema);

        /** Foreign keys generation **/
        $this->addOrocrmCaseForeignKeys($schema);
        $this->addOrocrmCaseCommentForeignKeys($schema);
        $this->addOroEmailMailboxProcessSettingsForeignKeys($schema);

        $this->addActivityAssociations($schema, $this->activityExtension);
        CreateActivityAssociation::addNoteAssociations($schema, $this->noteExtension);
        InheritanceActivityTargets::addInheritanceTargets($schema, $this->activityListExtension);
    }

    /**
     * Create orocrm_case table
     *
     * @param Schema $schema
     */
    protected function createOrocrmCaseTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_case');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('subject', 'string', ['length' => 255]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('resolution', 'text', ['notnull' => false]);
        $table->addColumn('related_contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_account_id', 'integer', ['notnull' => false]);
        $table->addColumn('assigned_to_id', 'integer', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('source_name', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('status_name', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('priority_name', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('updatedAt', 'datetime', ['notnull' => false]);
        $table->addColumn('reportedAt', 'datetime', []);
        $table->addColumn('closedAt', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'IDX_AB3BAC1E7E3C61F9', []);
        $table->addIndex(['organization_id'], 'IDX_AB3BAC1E32C8A3DE', []);
        $table->addIndex(['assigned_to_id'], 'IDX_AB3BAC1EF4BD7827', []);
        $table->addIndex(['related_contact_id'], 'IDX_AB3BAC1E6D6C2DFA', []);
        $table->addIndex(['related_account_id'], 'IDX_AB3BAC1E11A6570A', []);
        $table->addIndex(['source_name'], 'IDX_AB3BAC1E5FA9FB05', []);
        $table->addIndex(['priority_name'], 'IDX_AB3BAC1E965BD3DF', []);
    }

    /**
     * Create orocrm_case_source table
     *
     * @param Schema $schema
     */
    protected function createOrocrmCaseSourceTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_case_source');
        $table->addColumn('name', 'string', ['length' => 16]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->setPrimaryKey(['name']);
    }

    /**
     * Create orocrm_case_source_trans table
     *
     * @param Schema $schema
     */
    public static function createOrocrmCaseSourceTranslationTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_case_source_trans');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('foreign_key', 'string', ['length' => 16]);
        $table->addColumn('content', 'string', ['length' => 255]);
        $table->addColumn('locale', 'string', ['length' => 8]);
        $table->addColumn('object_class', 'string', ['length' => 255]);
        $table->addColumn('field', 'string', ['length' => 32]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(
            ['locale', 'object_class', 'field', 'foreign_key'],
            'case_source_translation_idx',
            []
        );
    }

    /**
     * Create orocrm_case_status table
     *
     * @param Schema $schema
     */
    protected function createOrocrmCaseStatusTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_case_status');
        $table->addColumn('name', 'string', ['length' => 16]);
        $table->addColumn('`order`', 'integer');
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->setPrimaryKey(['name']);
    }

    /**
     * Create orocrm_case_status_trans table
     *
     * @param Schema $schema
     */
    public static function createOrocrmCaseStatusTranslationTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_case_status_trans');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('foreign_key', 'string', ['length' => 16]);
        $table->addColumn('content', 'string', ['length' => 255]);
        $table->addColumn('locale', 'string', ['length' => 8]);
        $table->addColumn('object_class', 'string', ['length' => 255]);
        $table->addColumn('field', 'string', ['length' => 32]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(
            ['locale', 'object_class', 'field', 'foreign_key'],
            'case_status_translation_idx',
            []
        );
    }

    /**
     * Create orocrm_case_priority table
     *
     * @param Schema $schema
     */
    protected function createOrocrmCasePriorityTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_case_priority');
        $table->addColumn('name', 'string', ['length' => 16]);
        $table->addColumn('`order`', 'integer');
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->setPrimaryKey(['name']);
    }

    /**
     * Create orocrm_case_priority_trans table
     *
     * @param Schema $schema
     */
    public static function createOrocrmCasePriorityTranslationTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_case_priority_trans');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('foreign_key', 'string', ['length' => 16]);
        $table->addColumn('content', 'string', ['length' => 255]);
        $table->addColumn('locale', 'string', ['length' => 8]);
        $table->addColumn('object_class', 'string', ['length' => 255]);
        $table->addColumn('field', 'string', ['length' => 32]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(
            ['locale', 'object_class', 'field', 'foreign_key'],
            'case_priority_translation_idx',
            []
        );
    }

    /**
     * Create orocrm_case_comment table
     *
     * @param Schema $schema
     */
    public static function createOrocrmCaseCommentTranslationTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_case_comment');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('case_id', 'integer', ['notnull' => false]);
        $table->addColumn('updated_by_id', 'integer', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('message', 'text', []);
        $table->addColumn('public', 'boolean', ['default' => '0']);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('updatedAt', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['case_id'], 'IDX_604C70FBCF10D4F5', []);
        $table->addIndex(['contact_id'], 'IDX_604C70FBE7A1254A', []);
        $table->addIndex(['updated_by_id'], 'FK_604C70FB896DBBDE', []);
        $table->addIndex(['owner_id'], 'IDX_604C70FB7E3C61F9', []);
        $table->addIndex(['organization_id'], 'IDX_604C70FB32C8A3DE', []);
    }

    /**
     * Add orocrm_case foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmCaseForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_case');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            ['related_contact_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_account'),
            ['related_account_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_case_source'),
            ['source_name'],
            ['name'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_case_status'),
            ['status_name'],
            ['name'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_case_priority'),
            ['priority_name'],
            ['name'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['assigned_to_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Add orocrm_case_comment foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmCaseCommentForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_case_comment');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_case'),
            ['case_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['updated_by_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            ['contact_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );

        $this->attachmentExtension->addFileRelation(
            $schema,
            'orocrm_case_comment',
            'attachment'
        );
    }

    /**
     * Adds required columns to oro_email_mailbox_process table.
     *
     * @param Schema $schema
     */
    public static function addOroEmailMailboxProcessSettingsColumns(Schema $schema)
    {
        $table = $schema->getTable('oro_email_mailbox_process');

        $table->addColumn('case_assign_to_id', 'integer', ['notnull' => false]);
        $table->addColumn('case_status_name', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('case_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('case_priority_name', 'string', ['notnull' => false, 'length' => 16]);

        $table->addIndex(['case_owner_id'], 'IDX_CE8602A3E9411B84', []);
        $table->addIndex(['case_assign_to_id'], 'IDX_CE8602A37CFDD645', []);
        $table->addIndex(['case_priority_name'], 'IDX_CE8602A3F1B25087', []);
        $table->addIndex(['case_status_name'], 'IDX_CE8602A3C168B4FB', []);
    }

    /**
     * Adds foreign keys to new columns in oro_email_mailbox_process table.
     *
     * @param Schema $schema
     */
    public static function addOroEmailMailboxProcessSettingsForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_email_mailbox_process');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['case_assign_to_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_case_status'),
            ['case_status_name'],
            ['name'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['case_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_case_priority'),
            ['case_priority_name'],
            ['name'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Enables Email activity for Case entity
     *
     * @param Schema            $schema
     * @param ActivityExtension $activityExtension
     */
    protected function addActivityAssociations(Schema $schema, ActivityExtension $activityExtension)
    {
        $activityExtension->addActivityAssociation($schema, 'oro_email', 'orocrm_case');
        CreateActivityAssociation::addActivityAssociations($schema, $activityExtension);
    }
}
