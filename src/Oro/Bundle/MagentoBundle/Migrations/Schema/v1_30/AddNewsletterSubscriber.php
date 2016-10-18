<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_30;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddNewsletterSubscriber implements Migration, ExtendExtensionAwareInterface
{
    /** @var ExtendExtension $extendExtension */
    protected $extendExtension;

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
        $this->createOrocrmMagentoNewslSubscrTable($schema);
        $this->addOrocrmMagentoNewslSubscrForeignKeys($schema);
    }

    /**
     * Create oro_magento_newsl_subscr table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoNewslSubscrTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_newsl_subscr');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('customer_id', 'integer', ['notnull' => false]);
        $table->addColumn('store_id', 'integer', ['notnull' => false]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('email', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('change_status_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'precision' => 0, 'unsigned' => true]);
        $table->addColumn('confirm_code', 'string', ['notnull' => false, 'length' => 32]);
        $table->addIndex(['channel_id'], 'idx_7c8eaa72f5a1aa', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'idx_7c8eaa7e3c61f9', []);
        $table->addIndex(['store_id'], 'idx_7c8eaab092a811', []);
        $table->addIndex(['organization_id'], 'idx_7c8eaa32c8a3de', []);
        $table->addIndex(['data_channel_id'], 'idx_7c8eaabdc09b73', []);
        $table->addUniqueIndex(['customer_id'], 'uniq_7c8eaa9395c3f3');

        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'status',
            'mage_subscr_status',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM]
            ]
        );
    }

    /**
     * Add oro_magento_newsl_subscr foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMagentoNewslSubscrForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_newsl_subscr');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['owner_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_customer'),
            ['customer_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_store'),
            ['store_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
    }
}
