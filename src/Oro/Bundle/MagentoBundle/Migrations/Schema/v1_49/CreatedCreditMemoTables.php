<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_49;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtension;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareInterface;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class CreatedCreditMemoTables implements
    Migration,
    ActivityExtensionAwareInterface,
    ActivityListExtensionAwareInterface,
    ExtendExtensionAwareInterface
{
    /** @var ActivityExtension */
    protected $activityExtension;

    /** @var ActivityListExtension */
    protected $activityListExtension;

    /** @var ExtendExtension */
    protected $extendExtension;

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
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createCreditMemoTable($schema);
        $this->addCreditMemoForeignKeys($schema);
        $this->addCreditMemoActivities($schema);

        $this->createCreditMemoItemTable($schema);
        $this->addCreditMemoItemForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function createCreditMemoTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_credit_memo');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'unsigned' => true]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('increment_id', 'string', ['length' => 60]);
        $table->addColumn('invoice_id', 'integer', ['notnull' => false, 'unsigned' => true]);
        $table->addColumn('transaction_id', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('order_id', 'integer', ['notnull' => false]);
        $table->addColumn('store_id', 'integer', ['notnull' => false]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('is_email_sent', 'boolean', ['notnull' => false]);
        $table->addColumn(
            'shipping_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'adjustment',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'subtotal',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'adjustment_negative',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'grand_total',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'adjustment_positive',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'customer_bal_total_refunded',
            'float',
            ['notnull' => false, 'precision' => 0]
        );
        $table->addColumn(
            'reward_points_balance_refund',
            'float',
            ['notnull' => false, 'precision' => 0]
        );
        $table->addColumn('imported_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('synced_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['comment' => '(DC2Type:datetime)']);

        $this->extendExtension->addEnumField(
            $schema,
            'orocrm_magento_credit_memo',
            'status',
            'creditmemo_status',
            false,
            true,
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_TRUE],
            ]
        );

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['increment_id', 'channel_id'], 'unq_mcm_increment_id_channel_id');
        $table->addIndex(['created_at', 'id'], 'magecreditmemo_created_idx');
        $table->addIndex(['updated_at', 'id'], 'magecreditmemo_updated_idx');
    }

    /**
     * @param Schema $schema
     */
    protected function addCreditMemoForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_credit_memo');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
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
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_order'),
            ['order_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_store'),
            ['store_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }

    /**
     * @param Schema $schema
     */
    protected function addCreditMemoActivities(Schema $schema)
    {
        $this->activityExtension->addActivityAssociation($schema, 'oro_note', 'orocrm_magento_credit_memo');
        $this->activityListExtension->addActivityListAssociation($schema, 'orocrm_magento_credit_memo');
    }

    /**
     * @param Schema $schema
     */
    protected function createCreditMemoItemTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_creditmemo_item');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'unsigned' => true]);
        $table->addColumn('parent_id', 'integer', ['notnull' => false]);
        $table->addColumn('order_item_id', 'integer', ['notnull' => false, 'unsigned' => true]);
        $table->addColumn(
            'tax_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'discount_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'row_total',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn('qty', 'float', ['precision' => 0]);
        $table->addColumn(
            'price',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn('additional_data', 'text', ['notnull' => false]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('sku', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['origin_id'], 'magecreditmemoitem_origin_idx');
        $table->addIndex(['sku'], 'magecreditmemoitem_sku_idx');
    }

    /**
     * @param Schema $schema
     */
    protected function addCreditMemoItemForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_creditmemo_item');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_credit_memo'),
            ['parent_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
