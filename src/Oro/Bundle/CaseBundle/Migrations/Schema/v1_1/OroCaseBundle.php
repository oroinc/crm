<?php

namespace Oro\Bundle\CaseBundle\Migrations\Schema\v1_1;

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
        /** Generate table oro_case_comment **/
        $table = $schema->createTable('orocrm_case_comment');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('case_id', 'integer', ['notnull' => false]);
        $table->addColumn('updated_by_id', 'integer', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('message', 'text', []);
        $table->addColumn('public', 'boolean', ['default' => '0']);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('updatedAt', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['case_id'], 'IDX_604C70FBCF10D4F5', []);
        $table->addIndex(['contact_id'], 'IDX_604C70FBE7A1254A', []);
        $table->addIndex(['updated_by_id'], 'FK_604C70FB896DBBDE', []);
        $table->addIndex(['owner_id'], 'IDX_604C70FB7E3C61F9', []);
        /** End of generate table oro_case_comment **/

        /** Generate foreign keys for table oro_case_comment **/
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
        /** End of generate foreign keys for table oro_case_comment **/
    }
}
