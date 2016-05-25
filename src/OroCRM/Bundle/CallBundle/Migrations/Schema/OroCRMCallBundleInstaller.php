<?php

namespace OroCRM\Bundle\CallBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\CommentBundle\Migration\Extension\CommentExtension;
use Oro\Bundle\CommentBundle\Migration\Extension\CommentExtensionAwareInterface;
use Oro\Bundle\CallBundle\DoctrineExtensions\DBAL\Types\DurationType;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMCallBundleInstaller implements Installation, ActivityExtensionAwareInterface, CommentExtensionAwareInterface
{
    /** @var CommentExtension */
    protected $comment;

    /** @var ActivityExtension */
    protected $activityExtension;

    /**
     * @param CommentExtension $commentExtension
     */
    public function setCommentExtension(CommentExtension $commentExtension)
    {
        $this->comment = $commentExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_6';
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
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOrocrmCallTable($schema);
        $this->createOrocrmCallDirectionTable($schema);
        $this->createOrocrmCallStatusTable($schema);

        /** Foreign keys generation **/
        $this->addOrocrmCallForeignKeys($schema);

        $this->comment->addCommentAssociation($schema, 'orocrm_call');
    }

    /**
     * Create orocrm_call table
     *
     * @param Schema $schema
     */
    protected function createOrocrmCallTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_call');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('call_direction_name', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('call_status_name', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('subject', 'string', ['length' => 255]);
        $table->addColumn('phone_number', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('notes', 'text', ['notnull' => false]);
        $table->addColumn('call_date_time', 'datetime', []);
        $table->addColumn('duration', DurationType::getType('duration'), ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['organization_id'], 'IDX_1FBD1A2432C8A3DE', []);
        $table->addIndex(['owner_id'], 'IDX_1FBD1A247E3C61F9', []);
        $table->addIndex(['call_status_name'], 'IDX_1FBD1A2476DB3689', []);
        $table->addIndex(['call_direction_name'], 'IDX_1FBD1A249F3E257D', []);
        $table->addIndex(['call_date_time'], 'call_dt_idx');

        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'oro_user');
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_account');
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_contact');
    }

    /**
     * Create orocrm_call_direction table
     *
     * @param Schema $schema
     */
    protected function createOrocrmCallDirectionTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_call_direction');
        $table->addColumn('name', 'string', ['length' => 32]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->setPrimaryKey(['name']);
        $table->addUniqueIndex(['label'], 'UNIQ_D0EB34BAEA750E8');
    }

    /**
     * Create orocrm_call table
     *
     * @param Schema $schema
     */
    protected function createOrocrmCallStatusTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_call_status');
        $table->addColumn('name', 'string', ['length' => 32]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->setPrimaryKey(['name']);
        $table->addUniqueIndex(['label'], 'UNIQ_FBA13581EA750E8');
    }

    /**
     * Create orocrm_call table
     *
     * @param Schema $schema
     */
    protected function addOrocrmCallForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_call');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_call_direction'),
            ['call_direction_name'],
            ['name'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_call_status'),
            ['call_status_name'],
            ['name'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
