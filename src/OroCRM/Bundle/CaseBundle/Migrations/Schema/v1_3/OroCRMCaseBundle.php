<?php

namespace OroCRM\Bundle\CaseBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\SecurityBundle\Migrations\Schema\UpdateOwnershipTypeQuery;

class OroCRMCaseBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::addWorkflow($schema);
        self::dropStatusTable($schema, $queries);
    }

    /**
     * Adds workflow fields
     *
     * @param Schema $schema
     */
    public static function addWorkflow(Schema $schema)
    {
        $table = $schema->getTable('orocrm_case');
        $table->addColumn('workflow_item_id', 'integer', ['notnull' => false]);
        $table->addColumn('workflow_step_id', 'integer', ['notnull' => false]);

        $table->addUniqueIndex(['workflow_item_id'], 'UNIQ_AB3BAC1E1023C4EE');
        $table->addIndex(['workflow_step_id'], 'IDX_AB3BAC1E71FE882C', []);

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

    /**
     * Drops status related field and tables
     *
     * @param Schema $schema
     */
    public static function dropStatusTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_case');
        $table->removeForeignKey('fk_orocrm_case_status_name');
        $table->dropIndex('idx_orocrm_case_status_name');
        $table->dropColumn('status_name');

        if ($schema->hasTable('oro_entity_config_index_value') && $schema->hasTable('oro_entity_config_field')) {
            $queries->addPostQuery(
<<<DQL
            DELETE FROM oro_entity_config_field
            WHERE field_name = 'status'
            AND entity_id IN (
                SELECT id
                FROM oro_entity_config
                WHERE class_name = 'OroCRM\\\\Bundle\\\\CaseBundle\\\\Entity\\\\CaseEntity'
            );
DQL
            );
        }

        $schema->dropTable('orocrm_case_status');
        $schema->dropTable('orocrm_case_status_trans');
    }
}
