<?php

namespace OroCRM\Bundle\TaskBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMTaskBundle implements Migration
{
    protected $taskTableName = 'orocrm_task';
    protected $taskPriorityTableName = 'orocrm_task_priority';

    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->oroCrmCreateTaskPriorityTable($schema);
        $this->oroCrmCreateTaskTable($schema);
    }

    protected function oroCreateCrmTaskTableKeys(Schema $schema)
    {
        $taskTable = $schema->getTable($this->taskTableName);

        $taskTable->setPrimaryKey(['id']);

        $taskTable->addForeignKeyConstraint(
            $schema->getTable($this->taskPriorityTableName),
            ['task_priority_name'],
            ['name'],
            ['onDelete' => 'SET NULL']
        );

        $taskTable->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_step'),
            ['status_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );

        $taskTable->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['assigned_to_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );

        $taskTable->addForeignKeyConstraint(
            $schema->getTable('orocrm_account'),
            ['related_account_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );

        $taskTable->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            ['related_contact_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );

        $taskTable->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }

    protected function oroCrmCreateTaskTable(Schema $schema)
    {
        if ($schema->hasTable($this->taskTableName)) {
            $schema->dropTable($this->taskTableName);
        }

        $table = $schema->createTable($this->taskTableName);

        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('subject', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('description', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('due_date', 'datetime');
        $table->addColumn('createdAt', 'datetime');
        $table->addColumn('updatedAt', 'datetime');

        $table->addColumn('task_priority_name', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('status_id', 'integer', ['notnull' => false]);
        $table->addColumn('assigned_to_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_account_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);

        $this->oroCreateCrmTaskTableKeys($schema);
    }

    protected function oroCrmCreateTaskPriorityTableKeys(Schema $schema)
    {
        $priorityTable = $schema->getTable($this->taskPriorityTableName);
        $priorityTable->setPrimaryKey(['name']);
        $priorityTable->addUniqueIndex(['label']);
    }

    protected function oroCrmCreateTaskPriorityTable(Schema $schema)
    {
        if ($schema->hasTable($this->taskPriorityTableName)) {
            $schema->dropTable($this->taskPriorityTableName);
        }

        $priorityTable = $schema->createTable($this->taskPriorityTableName);

        $priorityTable->addColumn('name', 'string', ['notnull' => true, 'length' => 32]);
        $priorityTable->addColumn('label', 'string', ['notnull' => true, 'length' => 255]);

        $this->oroCrmCreateTaskPriorityTableKeys($schema);
    }
}
