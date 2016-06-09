<?php

namespace Oro\CRMCallBridgeBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtension;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareInterface;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtension;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareInterface;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtension;
use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtensionAwareInterface;

use OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_5\OroCRMSalesBundle as SalesNoteMigration;
use OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_7\OpportunityAttachment;
use OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_11\OroCRMSalesBundle as SalesOrganizations;
use OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_21\InheritanceActivityTargets;
use OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_22\AddOpportunityStatus;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class OroCRMCallBridgeBundleInstaller implements
    Installation,
    ExtendExtensionAwareInterface,
    NoteExtensionAwareInterface,
    ActivityExtensionAwareInterface,
    AttachmentExtensionAwareInterface,
    ActivityListExtensionAwareInterface
{
    /** @var ExtendExtension */
    protected $extendExtension;

    /** @var NoteExtension */
    protected $noteExtension;

    /** @var ActivityExtension */
    protected $activityExtension;

    /** @var AttachmentExtension */
    protected $attachmentExtension;

    /** @var ActivityListExtension */
    protected $activityListExtension;

    /**
     * {@inheritdoc}
     */
    public function setActivityListExtension(ActivityListExtension $activityListExtension)
    {
        $this->activityListExtension = $activityListExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function setNoteExtension(NoteExtension $noteExtension)
    {
        $this->noteExtension = $noteExtension;
    }

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
    public function setAttachmentExtension(AttachmentExtension $attachmentExtension)
    {
        $this->attachmentExtension = $attachmentExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_2';
    }

    public function up(Schema $schema, QueryBag $queries)
    {
        /** if CallBundle isn't installed  do nothing **/
        if (!$schema->hasTable('orocrm_call')) {
           return; 
        }

        /** Tables generation **/
        $this->createOrocrmContactusRequestCallsTable($schema);
        $this->createOrocrmMagentoOrderCallsTable($schema);
        $this->createOrocrmMagentoCartCallsTable($schema);

        /** Foreign keys generation **/
        $this->addOrocrmContactusRequestCallsForeignKeys($schema);
        $this->addOrocrmMagentoOrderCallsForeignKeys($schema);
        $this->addOrocrmMagentoCartCallsForeignKeys($schema);

        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_sales_lead');
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_sales_opportunity');
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_sales_b2bcustomer');
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_contactus_request');
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_magento_customer');
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_magento_order');
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_magento_cart');

        $associationTableName = $this->activityExtension->getAssociationTableName('orocrm_call', 'orocrm_case');
        if (!$schema->hasTable($associationTableName)) {
            $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_case');
        }
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

    /**
     * Create orocrm_magento_order_calls table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoOrderCallsTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_order_calls');
        $table->addColumn('order_id', 'integer', []);
        $table->addColumn('call_id', 'integer', []);
        $table->addIndex(['order_id'], 'IDX_A885A348D9F6D38', []);
        $table->addIndex(['call_id'], 'IDX_A885A3450A89B2C', []);
        $table->setPrimaryKey(['order_id', 'call_id']);
    }

    /**
     * Create orocrm_magento_cart_calls table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoCartCallsTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_cart_calls');
        $table->addColumn('cart_id', 'integer', []);
        $table->addColumn('call_id', 'integer', []);
        $table->addIndex(['cart_id'], 'IDX_83A847751AD5CDBF', []);
        $table->addIndex(['call_id'], 'IDX_83A8477550A89B2C', []);
        $table->setPrimaryKey(['cart_id', 'call_id']);
    }

    /**
     * Add orocrm_magento_order_calls foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMagentoOrderCallsForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_order_calls');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_order'),
            ['order_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_call'),
            ['call_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
    }

    /**
     * Add orocrm_magento_cart_calls foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMagentoCartCallsForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_cart_calls');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_cart'),
            ['cart_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_call'),
            ['call_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
    }

}