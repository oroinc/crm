<?php

namespace Oro\Bundle\ContactUsBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\ContactUsBundle\Migrations\Schema\v1_10\CreateActivityAssociation;
use Oro\Bundle\ContactUsBundle\Migrations\Schema\v1_7\OroContactUsBundle;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Executes all schema updates needed for correct work of bundle
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class OroContactUsBundleInstaller implements Installation, ActivityExtensionAwareInterface
{
    /** @var ActivityExtension */
    protected $activityExtension;

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
    public function getMigrationVersion()
    {
        return 'v1_18';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOrocrmContactusContactRsnTable($schema);
        $this->createOrocrmContactReasonTitlesTable($schema);
        $this->createOrocrmContactusReqEmailsTable($schema);
        $this->createOrocrmContactusRequestTable($schema);

        /** Foreign keys generation **/
        $this->addOrocrmContactusReqEmailsForeignKeys($schema);
        $this->addOrocrmContactusRequestForeignKeys($schema);

        OroContactUsBundle::addOwner($schema);

        CreateActivityAssociation::addEmailAssociations($schema, $this->activityExtension);
    }

    /**
     * Create oro_contactus_contact_rsn table
     */
    protected function createOrocrmContactusContactRsnTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_contactus_contact_rsn');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('deletedAt', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create oro_contactus_req_emails table
     */
    protected function createOrocrmContactusReqEmailsTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_contactus_req_emails');
        $table->addColumn('request_id', 'integer', []);
        $table->addColumn('email_id', 'integer', []);
        $table->setPrimaryKey(['request_id', 'email_id']);
        $table->addIndex(['request_id'], 'IDX_E494F7AE427EB8A5', []);
        $table->addIndex(['email_id'], 'IDX_E494F7AEA832C1C9', []);
    }

    /**
     * Create oro_contactus_request table
     */
    protected function createOrocrmContactusRequestTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_contactus_request');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('contact_reason_id', 'integer', ['notnull' => false]);
        $table->addColumn('lead_id', 'integer', ['notnull' => false]);
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
        $table->addIndex(['contact_reason_id'], 'IDX_342872E8374A36E9', []);
        $table->addIndex(['opportunity_id'], 'IDX_342872E89A34590F', []);
        $table->addIndex(['lead_id'], 'IDX_342872E855458D', []);
        $table->addIndex(['created_at', 'id'], 'request_create_idx', []);
    }

    /**
     * Add oro_contactus_req_emails foreign keys.
     */
    protected function addOrocrmContactusReqEmailsForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_contactus_req_emails');
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
    }

    /**
     * Add oro_contactus_request foreign keys.
     */
    protected function addOrocrmContactusRequestForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_contactus_request');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contactus_contact_rsn'),
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
            $schema->getTable('orocrm_sales_opportunity'),
            ['opportunity_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    protected function createOrocrmContactReasonTitlesTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_contactus_contact_rsn_t');
        $table->addColumn('contact_reason_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['contact_reason_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id']);

        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contactus_contact_rsn'),
            ['contact_reason_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
