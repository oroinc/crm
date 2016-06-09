<?php

namespace Oro\CRMCallBridgeBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;


class OroCRMCallBridgeBundleInstaller implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        /** if CallBundle isn't installed  do nothing **/
        if (!$schema->hasTable('orocrm_call')) {
            return;
        }

        self::orocrmMagentoCartCallsTable($schema);
        self::orocrmMagentoOrderCallsTable($schema);

        self::orocrmMagentoCartCallsForeignKeys($schema);
        self::orocrmMagentoOrderCallsForeignKeys($schema);
    }

    /**
     * Generate table orocrm_magento_cart_calls
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoCartCallsTable(Schema $schema)
    {
        /** Generate table orocrm_magento_cart_calls **/
        $table = $schema->createTable('orocrm_magento_cart_calls');
        $table->addColumn('cart_id', 'integer', []);
        $table->addColumn('call_id', 'integer', []);
        $table->setPrimaryKey(['cart_id', 'call_id']);
        $table->addIndex(['cart_id'], 'IDX_83A847751AD5CDBF', []);
        $table->addIndex(['call_id'], 'IDX_83A8477550A89B2C', []);
        /** End of generate table orocrm_magento_cart_calls **/
    }

    /**
     * Generate table orocrm_magento_order_calls
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoOrderCallsTable(Schema $schema)
    {
        /** Generate table orocrm_magento_order_calls **/
        $table = $schema->createTable('orocrm_magento_order_calls');
        $table->addColumn('order_id', 'integer', []);
        $table->addColumn('call_id', 'integer', []);
        $table->setPrimaryKey(['order_id', 'call_id']);
        $table->addIndex(['order_id'], 'IDX_A885A348D9F6D38', []);
        $table->addIndex(['call_id'], 'IDX_A885A3450A89B2C', []);
        /** End of generate table orocrm_magento_order_calls **/
    }

    /**
     * Generate foreign keys for table orocrm_magento_cart_calls
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoCartCallsForeignKeys(Schema $schema)
    {
        /** Generate foreign keys for table orocrm_magento_cart_calls **/
        $table = $schema->getTable('orocrm_magento_cart_calls');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_call'),
            ['call_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_cart'),
            ['cart_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        /** End of generate foreign keys for table orocrm_magento_cart_calls **/
    }
}