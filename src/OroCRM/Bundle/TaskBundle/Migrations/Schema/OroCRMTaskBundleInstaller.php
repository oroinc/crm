<?php

namespace OroCRM\Bundle\TaskBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\CommentBundle\Migration\Extension\CommentExtension;
use Oro\Bundle\CommentBundle\Migration\Extension\CommentExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use OroCRM\Bundle\TaskBundle\Migrations\Schema\v1_9\AddActivityAssociations;
use OroCRM\Bundle\TaskBundle\Migrations\Schema\v1_10\AddTaskStatusField;

class OroCRMTaskBundleInstaller implements
    Installation,
    ActivityExtensionAwareInterface,
    CommentExtensionAwareInterface,
    ExtendExtensionAwareInterface
{
    /** @var ActivityExtension */
    protected $activityExtension;

    /** @var CommentExtension */
    protected $comment;

    /** @var ExtendExtension */
    protected $extendExtension;

    /**
     * @param ActivityExtension $activityExtension
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }

    /**
     * @param CommentExtension $commentExtension
     */
    public function setCommentExtension(CommentExtension $commentExtension)
    {
        $this->comment = $commentExtension;
    }

    /**
     * @param ExtendExtension $extendExtension
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_10';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOrocrmTaskTable($schema);
        $this->createOrocrmTaskPriorityTable($schema);

        /** Foreign keys generation **/
        $this->addOrocrmTaskForeignKeys($schema);

        /** Add activity association */
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_task', 'orocrm_account');
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_task', 'orocrm_contact');

        /** Add comment relation */
        $this->comment->addCommentAssociation($schema, 'orocrm_task');

        AddActivityAssociations::addActivityAssociations($schema, $this->activityExtension);
        AddTaskStatusField::addTaskStatusField($schema, $this->extendExtension);
        AddTaskStatusField::addEnumValues($queries, $this->extendExtension);
    }

    /**
     * @param Schema $schema
     */
    protected function createOrocrmTaskTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_task');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('subject', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('due_date', 'datetime', ['notnull' => false]);
        $table->addColumn('task_priority_name', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('workflow_item_id', 'integer', ['notnull' => false]);
        $table->addColumn('workflow_step_id', 'integer', ['notnull' => false]);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('updatedAt', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['task_priority_name'], 'IDX_814DEE3FD34C1E8E', []);
        $table->addIndex(['owner_id'], 'IDX_814DEE3F7E3C61F9', []);
        $table->addIndex(['organization_id'], 'IDX_814DEE3F32C8A3DE', []);
        $table->addIndex(['due_date'], 'task_due_date_idx');
        $table->addUniqueIndex(['workflow_item_id'], 'UNIQ_814DEE3F1023C4EE');
        $table->addIndex(['workflow_step_id'], 'IDX_814DEE3F71FE882C', []);
        $table->addIndex(['updatedAt'], 'task_updated_at_idx', []);
    }

    /**
     * @param Schema $schema
     */
    protected function createOrocrmTaskPriorityTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_task_priority');
        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 32]);
        $table->addColumn('label', 'string', ['notnull' => true, 'length' => 255]);
        $table->addColumn('`order`', 'integer', ['notnull' => true]);
        $table->setPrimaryKey(['name']);
        $table->addUniqueIndex(['label'], 'UNIQ_DB8472D3EA750E8');
    }

    /**
     * @param Schema $schema
     */
    protected function addOrocrmTaskForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_task');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_task_priority'),
            ['task_priority_name'],
            ['name'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_item'),
            ['workflow_item_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_step'),
            ['workflow_step_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }
}
