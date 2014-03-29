<?php

namespace OroCRM\Bundle\TaskBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMTaskBundle implements Migration
{
    /**
     * @var string
     */
    protected $taskTableName = 'orocrm_task';

    /**
     * @var string
     */
    protected $taskPriorityTableName = 'orocrm_task_priority';

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->oroCrmCreateTaskPriorityTable($schema);
        $this->oroCrmCreateTaskTable($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function oroCrmCreateTaskPriorityTable(Schema $schema)
    {
        if ($schema->hasTable($this->taskPriorityTableName)) {
            $schema->dropTable($this->taskPriorityTableName);
        }

        $table = $schema->createTable($this->taskPriorityTableName);

        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 32]);
        $table->addColumn('label', 'string', ['notnull' => true, 'length' => 255]);
        $table->addColumn('`order`', 'integer', ['notnull' => true]);

        $table->setPrimaryKey(['name']);
        $table->addUniqueIndex(['label'], 'UNIQ_DB8472D3EA750E8');
    }

    /**
     * @param Schema $schema
     */
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
        $table->addColumn('task_priority_name', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_account_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('reporter_id', 'integer', ['notnull' => false]);
        $table->addColumn('workflow_item_id', 'integer', ['notnull' => false]);
        $table->addColumn('workflow_step_id', 'integer', ['notnull' => false]);
        $table->addColumn('createdAt', 'datetime');
        $table->addColumn('updatedAt', 'datetime', ['notnull' => false]);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['task_priority_name'], 'IDX_814DEE3FD34C1E8E', []);
        $table->addIndex(['owner_id'], 'IDX_814DEE3F7E3C61F9', []);
        $table->addIndex(['related_account_id'], 'IDX_814DEE3FE774F01D', []);
        $table->addIndex(['related_contact_id'], 'IDX_814DEE3FD6204081', []);
        $table->addIndex(['reporter_id'], 'IDX_814DEE3FE1CFE6F5', []);
        $table->addIndex(['due_date'], 'task_due_date_idx');
        $table->addUniqueIndex(['workflow_item_id'], 'UNIQ_814DEE3F1023C4EE');
        $table->addIndex(['workflow_step_id'], 'IDX_814DEE3F71FE882C', []);

        $this->oroCrmCreateTaskTableForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function oroCrmCreateTaskTableForeignKeys(Schema $schema)
    {
        $table = $schema->getTable($this->taskTableName);

        $table->addForeignKeyConstraint(
            $schema->getTable($this->taskPriorityTableName),
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
            $schema->getTable('orocrm_account'),
            ['related_account_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            ['related_contact_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['reporter_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
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
