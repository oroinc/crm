<?php

namespace OroCRM\Bundle\MarketingListBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class OroCRMMarketingListBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_2';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOrocrmMarketingListTypeTable($schema);
        $this->createOrocrmMarketingListTable($schema);
        $this->createOrocrmMlItemUnsTable($schema);
        $this->createOrocrmMarketingListItemTable($schema);
        $this->createOrocrmMlItemRmTable($schema);

        /** Foreign keys generation **/
        $this->addOrocrmMarketingListForeignKeys($schema);
        $this->addOrocrmMlItemUnsForeignKeys($schema);
        $this->addOrocrmMarketingListItemForeignKeys($schema);
        $this->addOrocrmMlItemRmForeignKeys($schema);
    }

    /**
     * Create orocrm_marketing_list_type table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMarketingListTypeTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_marketing_list_type');
        $table->addColumn('name', 'string', ['length' => 32]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->addUniqueIndex(['label'], 'uniq_143b81a8ea750e8');
        $table->setPrimaryKey(['name']);
    }

    /**
     * Create orocrm_marketing_list table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMarketingListTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_marketing_list');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('type', 'string', ['length' => 32]);
        $table->addColumn('segment_id', 'integer', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('entity', 'string', ['length' => 255]);
        $table->addColumn('last_run', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addIndex(['owner_id'], 'idx_3acc3ba7e3c61f9', []);
        $table->addIndex(['segment_id'], 'idx_3acc3badb296aad', []);
        $table->addIndex(['type'], 'idx_3acc3ba8cde5729', []);
        $table->addIndex(['organization_id'], 'idx_3acc3ba32c8a3de', []);
        $table->addUniqueIndex(['name'], 'uniq_3acc3ba5e237e06');
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create orocrm_ml_item_uns table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMlItemUnsTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_ml_item_uns');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('marketing_list_id', 'integer', []);
        $table->addColumn('entity_id', 'integer', []);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addUniqueIndex(['entity_id', 'marketing_list_id'], 'orocrm_ml_list_ent_uns_unq');
        $table->addIndex(['marketing_list_id'], 'idx_ceb0306896434d04', []);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create orocrm_marketing_list_item table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMarketingListItemTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_marketing_list_item');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('marketing_list_id', 'integer', []);
        $table->addColumn('entity_id', 'integer', []);
        $table->addColumn('contacted_times', 'integer', ['notnull' => false]);
        $table->addColumn('last_contacted_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addIndex(['marketing_list_id'], 'idx_87fef39f96434d04', []);
        $table->addUniqueIndex(['entity_id', 'marketing_list_id'], 'orocrm_ml_list_ent_unq');
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create orocrm_ml_item_rm table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMlItemRmTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_ml_item_rm');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('marketing_list_id', 'integer', []);
        $table->addColumn('entity_id', 'integer', []);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['marketing_list_id'], 'idx_8f6405f96434d04', []);
        $table->addUniqueIndex(['entity_id', 'marketing_list_id'], 'orocrm_ml_list_ent_rm_unq');
    }

    /**
     * Add orocrm_marketing_list foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMarketingListForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_marketing_list');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_marketing_list_type'),
            ['type'],
            ['name'],
            ['onUpdate' => null, 'onDelete' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_segment'),
            ['segment_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['owner_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
    }

    /**
     * Add orocrm_ml_item_uns foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMlItemUnsForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_ml_item_uns');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_marketing_list'),
            ['marketing_list_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add orocrm_marketing_list_item foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMarketingListItemForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_marketing_list_item');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_marketing_list'),
            ['marketing_list_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add orocrm_ml_item_rm foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMlItemRmForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_ml_item_rm');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_marketing_list'),
            ['marketing_list_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }
}
