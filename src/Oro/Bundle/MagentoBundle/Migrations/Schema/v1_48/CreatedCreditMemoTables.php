<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_48;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class CreatedCreditMemoTables implements Migration, ActivityExtensionAwareInterface
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
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createCreditMemoTable($schema, $this->activityExtension);
        $this->addCreditMemoForeignKeys($schema);
        $this->addCreditMemoActivities($schema);
        $this->updateOrderAddressTable($schema);
        $this->createCreditMemoItemTable($schema);
    }

    /**
     * @param Schema $schema
     *
     * @param ActivityExtension $activityExtension
     */
    protected function createCreditMemoTable(Schema $schema, ActivityExtension $activityExtension)
    {
        $table = $schema->createTable('orocrm_magento_credit_memo');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('increment_id', 'string', ['length' => 60]);
        $table->addColumn('credit_memo_id', 'string', ['length' => 60, 'notnull' => false]);
        $table->addColumn('invoice_id', 'string', ['length' => 60, 'notnull' => false]);
        $table->addColumn('transaction_id', 'string', ['length' => 60, 'notnull' => false]);
        $table->addColumn('order_id', 'integer', ['notnull' => false]);
        $table->addColumn('store_id', 'integer', ['notnull' => false]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('status', 'string', ['length' => 32, 'notnull' => false]);
        $table->addColumn('is_email_sent', 'boolean', ['notnull' => false]);
        $table->addColumn('global_currency_code', 'string', ['length' => 32, 'notnull' => false]);
        $table->addColumn('base_currency_code', 'string', ['length' => 32, 'notnull' => false]);
        $table->addColumn('order_currency_code', 'string', ['length' => 32, 'notnull' => false]);
        $table->addColumn('store_currency_code', 'string', ['length' => 32, 'notnull' => false]);
        $table->addColumn('cybersource_token', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('state', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn(
            'tax_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'shipping_tax_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'base_tax_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'base_adjustment_positive',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'base_grand_total',
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
            'discount_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'base_subtotal',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'base_adjustment',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'base_to_global_rate',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'store_to_base_rate',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'base_shipping_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'adjustment_negative',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'subtotal_incl_tax',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'shipping_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'base_subtotal_incl_tax',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'base_adjustment_negative',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'grand_total',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'base_discount_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'base_to_order_rate',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'store_to_order_rate',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'base_shipping_tax_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'adjustment_positive',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'hidden_tax_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'base_hidden_tax_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'shipping_hidden_tax_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'base_shipping_hidden_tax_amnt',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'shipping_incl_tax',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'base_shipping_incl_tax',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'base_customer_balance_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'customer_balance_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'bs_customer_bal_total_refunded',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'customer_bal_total_refunded',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'base_gift_cards_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'gift_cards_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'gw_base_price',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'gw_price',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'gw_items_base_price',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'gw_items_price',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'gw_card_base_price',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'gw_card_price',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'gw_base_tax_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'gw_tax_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'gw_items_base_tax_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'gw_items_tax_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'gw_card_base_tax_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'gw_card_tax_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'base_reward_currency_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'reward_currency_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'reward_points_balance',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'reward_points_balance_refund',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn('created_at', 'datetime', ['precision' => 0]);
        $table->addColumn('updated_at', 'datetime', ['precision' => 0]);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['increment_id', 'channel_id'], 'unq_mcm_increment_id_channel_id');
        $table->addIndex(['created_at', 'id'], 'magecreditmemo_created_idx');
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
            ['onDelete' => 'SET NULL']
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
    }

    /**
     * @param Schema $schema
     */
    protected function updateOrderAddressTable(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_order_address');
        $table->addColumn('credit_memo_id', 'integer', ['notnull' => false]);
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_credit_memo'),
            ['credit_memo_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }

    /**
     * @param Schema $schema
     */
    protected function createCreditMemoItemTable(Schema $schema)
    {
        // @TODO: Implement in SHOE-9.
    }
}
