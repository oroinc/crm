<?php

namespace OroCRM\Bundle\ContactUsBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;

use OroCRM\Bundle\ContactUsBundle\Migrations\Schema\v1_7\OroCRMContactUsBundle;
use OroCRM\Bundle\ContactUsBundle\Migrations\Schema\v1_10\CreateActivityAssociation;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class OroCRMContactUsBundleInstaller implements Installation, ActivityExtensionAwareInterface
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
        return 'v1_11';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOrocrmContactusContactRsnTable($schema);
        $this->createOrocrmContactusReqEmailsTable($schema);
        $this->createOrocrmContactusRequestTable($schema);
        $this->createOrocrmContactusRequestCallsTable($schema);

        /** Foreign keys generation **/
        $this->addOrocrmContactusReqEmailsForeignKeys($schema);
        $this->addOrocrmContactusRequestForeignKeys($schema);
        $this->addOrocrmContactusRequestCallsForeignKeys($schema);
        OroCRMContactUsBundle::addOwner($schema);

        CreateActivityAssociation::addActivityAssociations($schema, $this->activityExtension);
    }

    /**
     * Create orocrm_contactus_contact_rsn table
     *
     * @param Schema $schema
     */
    protected function createOrocrmContactusContactRsnTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_contactus_contact_rsn');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create orocrm_contactus_req_emails table
     *
     * @param Schema $schema
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
     * Create orocrm_contactus_request table
     *
     * @param Schema $schema
     */
    protected function createOrocrmContactusRequestTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_contactus_request');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('contact_reason_id', 'integer', ['notnull' => false]);
        $table->addColumn('lead_id', 'integer', ['notnull' => false]);
        $table->addColumn('opportunity_id', 'integer', ['notnull' => false]);
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
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
        $table->addIndex(['data_channel_id'], 'IDX_342872E8BDC09B73', []);
        $table->addIndex(['opportunity_id'], 'IDX_342872E89A34590F', []);
        $table->addIndex(['lead_id'], 'IDX_342872E855458D', []);
        $table->addIndex(['created_at'], 'request_create_idx', []);
    }

    /**
     * Create orocrm_contactus_request_calls table
     *
     * @param Schema $schema
     */
    protected function createOrocrmContactusRequestCallsTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_contactus_request_calls');
        $table->addColumn('request_id', 'integer', []);
        $table->addColumn('call_id', 'integer', []);
        $table->setPrimaryKey(['request_id', 'call_id']);
        $table->addIndex(['request_id'], 'IDX_6F7A50CE427EB8A5', []);
        $table->addIndex(['call_id'], 'IDX_6F7A50CE50A89B2C', []);
    }

    /**
     * Add orocrm_contactus_req_emails foreign keys.
     *
     * @param Schema $schema
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
     * Add orocrm_contactus_request foreign keys.
     *
     * @param Schema $schema
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
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_342872E8BDC09B73'
        );
    }

    /**
     * Add orocrm_contactus_request_calls foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmContactusRequestCallsForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_contactus_request_calls');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_call'),
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
    }
}
