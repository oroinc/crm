<?php

namespace Oro\Bundle\ContactUsBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroContactUsBundle implements Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::orocrmContactusContactReasonTable($schema);
        self::orocrmContactusRequestTable($schema);
        self::orocrmContactusRequestCallsTable($schema);
        self::orocrmContactusRequestEmailsTable($schema);

        self::orocrmContactusRequestForeignKeys($schema);
        self::orocrmContactusRequestCallsForeignKeys($schema);
        self::orocrmContactusRequestEmailsForeignKeys($schema);
    }

    /**
     * Generate table oro_contactus_contact_reason
     *
     * @param Schema $schema
     * @param string $tableName
     */
    public static function orocrmContactusContactReasonTable(Schema $schema, $tableName = null)
    {
        /** Generate table oro_contactus_contact_reason **/
        $table = $schema->createTable($tableName ?: 'orocrm_contactus_contact_reason');
        $table->addColumn('id', 'smallint', ['autoincrement' => true]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
        /** End of generate table oro_contactus_contact_reason **/
    }

    /**
     * Generate table oro_contactus_request
     */
    public static function orocrmContactusRequestTable(Schema $schema)
    {
        /** Generate table oro_contactus_request **/
        $table = $schema->createTable('orocrm_contactus_request');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('channel_id', 'smallint', ['notnull' => false]);
        $table->addColumn('workflow_item_id', 'integer', ['notnull' => false]);
        $table->addColumn('contact_reason_id', 'smallint', ['notnull' => false]);
        $table->addColumn('lead_id', 'integer', ['notnull' => false]);
        $table->addColumn('workflow_step_id', 'integer', ['notnull' => false]);
        $table->addColumn('opportunity_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('preferred_contact_method', 'string', ['length' => 100]);
        $table->addColumn('feedback', 'text', ['notnull' => false]);
        $table->addColumn('first_name', 'string', ['length' => 100]);
        $table->addColumn('last_name', 'string', ['length' => 100]);
        $table->addColumn('email_address', 'string', ['notnull' => false, 'length' => 100]);
        $table->addColumn('phone', 'string', ['notnull' => false, 'length' => 100]);
        $table->addColumn('comment', 'text', []);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['workflow_item_id'], 'UNIQ_342872E81023C4EE');
        $table->addIndex(['contact_reason_id'], 'IDX_342872E8374A36E9', []);
        $table->addIndex(['opportunity_id'], 'IDX_342872E89A34590F', []);
        $table->addIndex(['lead_id'], 'IDX_342872E855458D', []);
        $table->addIndex(['workflow_step_id'], 'IDX_342872E871FE882C', []);
        $table->addIndex(['channel_id'], 'IDX_342872E872F5A1AA', []);
        /** End of generate table oro_contactus_request **/
    }

    /**
     * Generate table oro_contactus_request_calls
     */
    public static function orocrmContactusRequestCallsTable(Schema $schema)
    {
        /** Generate table oro_contactus_request_calls **/
        $table = $schema->createTable('orocrm_contactus_request_calls');
        $table->addColumn('request_id', 'integer', []);
        $table->addColumn('call_id', 'integer', []);
        $table->setPrimaryKey(['request_id', 'call_id']);
        $table->addIndex(['request_id'], 'IDX_6F7A50CE427EB8A5', []);
        $table->addIndex(['call_id'], 'IDX_6F7A50CE50A89B2C', []);
        /** End of generate table oro_contactus_request_calls **/
    }

    /**
     * Generate table oro_contactus_request_emails
     *
     * @param Schema $schema
     * @param string $tableName
     */
    public static function orocrmContactusRequestEmailsTable(Schema $schema, $tableName = null)
    {
        /** Generate table oro_contactus_request_emails **/
        $table = $schema->createTable($tableName ?: 'orocrm_contactus_request_emails');
        $table->addColumn('request_id', 'integer', []);
        $table->addColumn('email_id', 'integer', []);
        $table->setPrimaryKey(['request_id', 'email_id']);
        $table->addIndex(['request_id'], 'IDX_4DEF4058427EB8A5', []);
        $table->addIndex(['email_id'], 'IDX_4DEF4058A832C1C9', []);
        /** End of generate table oro_contactus_request_emails **/
    }

    /**
     * Generate foreign keys for table oro_contactus_request
     *
     * @param Schema $schema
     * @param string $contactReasonTableName
     */
    public static function orocrmContactusRequestForeignKeys(Schema $schema, $contactReasonTableName = null)
    {
        /** Generate foreign keys for table oro_contactus_request **/
        $table = $schema->getTable('orocrm_contactus_request');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_item'),
            ['workflow_item_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable($contactReasonTableName ?: 'orocrm_contactus_contact_reason'),
            ['contact_reason_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_lead'),
            ['lead_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_step'),
            ['workflow_step_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_opportunity'),
            ['opportunity_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        /** End of generate foreign keys for table oro_contactus_request **/
    }

    /**
     * Generate foreign keys for table oro_contactus_request_calls
     */
    public static function orocrmContactusRequestCallsForeignKeys(Schema $schema)
    {
        /** Generate foreign keys for table oro_contactus_request_calls **/
        $table = $schema->getTable('orocrm_contactus_request_calls');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_call'),
            ['call_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contactus_request'),
            ['request_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        /** End of generate foreign keys for table oro_contactus_request_calls **/
    }

    /**
     * Generate foreign keys for table oro_contactus_request_emails
     *
     * @param Schema $schema
     * @param string $tableName
     */
    public static function orocrmContactusRequestEmailsForeignKeys(Schema $schema, $tableName = null)
    {
        /** Generate foreign keys for table oro_contactus_request_emails **/
        $table = $schema->getTable($tableName ?: 'orocrm_contactus_request_emails');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_email'),
            ['email_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contactus_request'),
            ['request_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        /** End of generate foreign keys for table oro_contactus_request_emails **/
    }
}
