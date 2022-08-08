<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroSalesBundle implements Migration, ExtendExtensionAwareInterface
{
    /**
     * @var ExtendExtension
     */
    protected $extendExtension;

    /**
     * @inheritdoc
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::orocrmSalesLeadTable($schema, $this->extendExtension);
        self::orocrmSalesLeadStatusTable($schema);
        self::orocrmSalesOpportunityTable($schema);
        self::orocrmSalesOpportunityCloseReasonTable($schema);
        self::orocrmSalesOpportunityStatusTable($schema);

        self::orocrmSalesLeadForeignKeys($schema);
        self::orocrmSalesOpportunityForeignKeys($schema);
    }

    /**
     * Generate table oro_sales_lead
     */
    public static function orocrmSalesLeadTable(Schema $schema, ExtendExtension $extendExtension)
    {
        /** Generate table oro_sales_lead **/
        $table = $schema->createTable('orocrm_sales_lead');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('workflow_step_id', 'integer', ['notnull' => false]);
        $table->addColumn('workflow_item_id', 'integer', ['notnull' => false]);
        $table->addColumn('status_name', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('account_id', 'integer', ['notnull' => false]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('address_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('name_prefix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('first_name', 'string', ['length' => 255]);
        $table->addColumn('middle_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('last_name', 'string', ['length' => 255]);
        $table->addColumn('name_suffix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('job_title', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('phone_number', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('email', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('company_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('website', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('number_of_employees', 'integer', ['notnull' => false]);
        $table->addColumn('industry', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('updatedAt', 'datetime', ['notnull' => false]);
        $table->addColumn('notes', 'text', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['workflow_item_id'], 'UNIQ_73DB46331023C4EE');
        $table->addIndex(['status_name'], 'IDX_73DB46336625D392', []);
        $table->addIndex(['contact_id'], 'IDX_73DB4633E7A1254A', []);
        $table->addIndex(['account_id'], 'IDX_73DB46339B6B5FBA', []);
        $table->addIndex(['address_id'], 'IDX_73DB4633F5B7AF75', []);
        $table->addIndex(['user_owner_id'], 'IDX_73DB46339EB185F9', []);
        $table->addIndex(['workflow_step_id'], 'IDX_73DB463371FE882C', []);
        /** End of generate table oro_sales_lead **/
    }

    /**
     * Generate table oro_sales_lead_status
     */
    public static function orocrmSalesLeadStatusTable(Schema $schema)
    {
        /** Generate table oro_sales_lead_status **/
        $table = $schema->createTable('orocrm_sales_lead_status');
        $table->addColumn('name', 'string', ['length' => 32]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->setPrimaryKey(['name']);
        $table->addUniqueIndex(['label'], 'UNIQ_4516951BEA750E8');
        /** End of generate table oro_sales_lead_status **/
    }

    /**
     * Generate table oro_sales_lead_status
     */
    public static function orocrmSalesOpportunityTable(Schema $schema)
    {
        /** Generate table oro_sales_opportunity **/
        $table = $schema->createTable('orocrm_sales_opportunity');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('workflow_step_id', 'integer', ['notnull' => false]);
        $table->addColumn('workflow_item_id', 'integer', ['notnull' => false]);
        $table->addColumn('lead_id', 'integer', ['notnull' => false]);
        $table->addColumn('status_name', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('account_id', 'integer', ['notnull' => false]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('close_reason_name', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('close_date', 'date', ['notnull' => false]);
        $table->addColumn('probability', 'float', ['notnull' => false]);
        $table->addColumn('budget_amount', 'float', ['notnull' => false]);
        $table->addColumn('close_revenue', 'float', ['notnull' => false]);
        $table->addColumn('customer_need', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('proposed_solution', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->addColumn('notes', 'text', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['workflow_item_id'], 'UNIQ_C0FE4AAC1023C4EE');
        $table->addIndex(['status_name'], 'IDX_C0FE4AAC6625D392', []);
        $table->addIndex(['close_reason_name'], 'IDX_C0FE4AACD81B931C', []);
        $table->addIndex(['contact_id'], 'IDX_C0FE4AACE7A1254A', []);
        $table->addIndex(['account_id'], 'IDX_C0FE4AAC9B6B5FBA', []);
        $table->addIndex(['lead_id'], 'IDX_C0FE4AAC55458D', []);
        $table->addIndex(['user_owner_id'], 'IDX_C0FE4AAC9EB185F9', []);
        $table->addIndex(['workflow_step_id'], 'IDX_C0FE4AAC71FE882C', []);
        /** End of generate table oro_sales_opportunity **/
    }

    /**
     * Generate table oro_sales_opportunity_close_reason
     *
     * @param Schema $schema
     * @param string $tableName
     */
    public static function orocrmSalesOpportunityCloseReasonTable(Schema $schema, $tableName = null)
    {
        /** Generate table oro_sales_opportunity_close_reason **/
        $table = $schema->createTable($tableName ?: 'orocrm_sales_opportunity_close_reason');
        $table->addColumn('name', 'string', ['length' => 32]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->setPrimaryKey(['name']);
        $table->addUniqueIndex(['label'], 'UNIQ_FA526A41EA750E8');
        /** End of generate table oro_sales_opportunity_close_reason **/
    }

    /**
     * Generate table oro_sales_opportunity_status
     *
     * @param Schema $schema
     * @param string $tableName
     */
    public static function orocrmSalesOpportunityStatusTable(Schema $schema, $tableName = null)
    {
        /** Generate table oro_sales_opportunity_status **/
        $table = $schema->createTable($tableName ?: 'orocrm_sales_opportunity_status');
        $table->addColumn('name', 'string', ['length' => 32]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->setPrimaryKey(['name']);
        $table->addUniqueIndex(['label'], 'UNIQ_2DB212B5EA750E8');
        /** End of generate table oro_sales_opportunity_status **/
    }

    /**
     * Generate foreign keys for table oro_sales_lead
     */
    public static function orocrmSalesLeadForeignKeys(Schema $schema)
    {
        /** Generate foreign keys for table oro_sales_lead **/
        $table = $schema->getTable('orocrm_sales_lead');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_step'),
            ['workflow_step_id'],
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
            $schema->getTable('orocrm_sales_lead_status'),
            ['status_name'],
            ['name'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_account'),
            ['account_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
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
            $schema->getTable('oro_address'),
            ['address_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        /** End of generate foreign keys for table oro_sales_lead **/
    }

    /**
     * Generate foreign keys for table oro_sales_opportunity
     *
     * @param Schema $schema
     * @param string $closeReasonTableName
     * @param string $opportunityStatusTableName
     */
    public static function orocrmSalesOpportunityForeignKeys(
        Schema $schema,
        $closeReasonTableName = null,
        $opportunityStatusTableName = null
    ) {
        /** Generate foreign keys for table oro_sales_opportunity **/
        $table = $schema->getTable('orocrm_sales_opportunity');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_step'),
            ['workflow_step_id'],
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
            $schema->getTable('orocrm_sales_lead'),
            ['lead_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable($opportunityStatusTableName ?: 'orocrm_sales_opportunity_status'),
            ['status_name'],
            ['name'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_account'),
            ['account_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable($closeReasonTableName ?: 'orocrm_sales_opportunity_close_reason'),
            ['close_reason_name'],
            ['name'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            ['contact_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        /** End of generate foreign keys for table oro_sales_opportunity **/
    }
}
