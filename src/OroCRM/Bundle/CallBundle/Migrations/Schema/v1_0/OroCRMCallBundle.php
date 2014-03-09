<?php

namespace OroCRM\Bundle\CallBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMCallBundle implements Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        // @codingStandardsIgnoreStart

        /** Generate table orocrm_call **/
        $table = $schema->createTable('orocrm_call');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('call_direction_name', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('related_account_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('call_status_name', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('contact_phone_id', 'integer', ['notnull' => false]);
        $table->addColumn('subject', 'string', ['length' => 255]);
        $table->addColumn('phone_number', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('notes', 'text', ['notnull' => false]);
        $table->addColumn('call_date_time', 'datetime', []);
        $table->addColumn('duration', 'time', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'IDX_1FBD1A247E3C61F9', []);
        $table->addIndex(['related_contact_id'], 'IDX_1FBD1A246D6C2DFA', []);
        $table->addIndex(['related_account_id'], 'IDX_1FBD1A2411A6570A', []);
        $table->addIndex(['contact_phone_id'], 'IDX_1FBD1A24A156BF5C', []);
        $table->addIndex(['call_status_name'], 'IDX_1FBD1A2476DB3689', []);
        $table->addIndex(['call_direction_name'], 'IDX_1FBD1A249F3E257D', []);
        /** End of generate table orocrm_call **/

        /** Generate table orocrm_call_direction **/
        $table = $schema->createTable('orocrm_call_direction');
        $table->addColumn('name', 'string', ['length' => 32]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->setPrimaryKey(['name']);
        $table->addUniqueIndex(['label'], 'UNIQ_D0EB34BAEA750E8');
        /** End of generate table orocrm_call_direction **/

        /** Generate table orocrm_call_status **/
        $table = $schema->createTable('orocrm_call_status');
        $table->addColumn('name', 'string', ['length' => 32]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->setPrimaryKey(['name']);
        $table->addUniqueIndex(['label'], 'UNIQ_FBA13581EA750E8');
        /** End of generate table orocrm_call_status **/

        /** Generate foreign keys for table orocrm_call **/
        $table = $schema->getTable('orocrm_call');
        $table->addForeignKeyConstraint($schema->getTable('orocrm_call_direction'), ['call_direction_name'], ['name'], ['onDelete' => 'SET NULL', 'onUpdate' => null]);
        $table->addForeignKeyConstraint($schema->getTable('orocrm_account'), ['related_account_id'], ['id'], ['onDelete' => 'SET NULL', 'onUpdate' => null]);
        $table->addForeignKeyConstraint($schema->getTable('orocrm_contact'), ['related_contact_id'], ['id'], ['onDelete' => 'SET NULL', 'onUpdate' => null]);
        $table->addForeignKeyConstraint($schema->getTable('orocrm_call_status'), ['call_status_name'], ['name'], ['onDelete' => 'SET NULL', 'onUpdate' => null]);
        $table->addForeignKeyConstraint($schema->getTable('oro_user'), ['owner_id'], ['id'], ['onDelete' => 'SET NULL', 'onUpdate' => null]);
        $table->addForeignKeyConstraint($schema->getTable('orocrm_contact_phone'), ['contact_phone_id'], ['id'], ['onDelete' => 'SET NULL', 'onUpdate' => null]);
        /** End of generate foreign keys for table orocrm_call **/

        // @codingStandardsIgnoreEnd
    }
}
