<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCrmMagentoBundle implements Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_magento_order_items');
        $table->getColumn('original_price')->setType(Type::getType('money'));
        $table->getColumn('discount_percent')->setType(Type::getType('percent'));
        $table->getColumn('price')->setType(Type::getType('money'));
        $table->getColumn('tax_percent')->setType(Type::getType('percent'));
        $table->getColumn('tax_amount')->setType(Type::getType('money'));
        $table->getColumn('discount_amount')->setType(Type::getType('money'));
        $table->getColumn('row_total')->setType(Type::getType('money'));

        $table = $schema->getTable('orocrm_magento_product');
        $table->getColumn('special_price')->setType(Type::getType('money'));
        $table->getColumn('price')->setType(Type::getType('money'));
        $table->getColumn('cost')->setType(Type::getType('money'));

        $table = $schema->getTable('orocrm_magento_cart');
        $table->getColumn('sub_total')->setType(Type::getType('money'));
        $table->getColumn('grand_total')->setType(Type::getType('money'));
        $table->getColumn('tax_amount')->setType(Type::getType('money'));

        $table = $schema->getTable('orocrm_magento_order');
        $table->getColumn('total_invoiced_amount')->setType(Type::getType('money'));
        $table->getColumn('total_refunded_amount')->setType(Type::getType('money'));
        $table->getColumn('total_canceled_amount')->setType(Type::getType('money'));
        $table->getColumn('subtotal_amount')->setType(Type::getType('money'));
        $table->getColumn('shipping_amount')->setType(Type::getType('money'));
        $table->getColumn('tax_amount')->setType(Type::getType('money'));
        $table->getColumn('discount_amount')->setType(Type::getType('money'));
        $table->getColumn('total_amount')->setType(Type::getType('money'));
        $table->getColumn('discount_percent')->setType(Type::getType('percent'));

        $table = $schema->getTable('orocrm_magento_cart_item');
        $table->getColumn('custom_price')->setType(Type::getType('money'));
        $table->getColumn('price_incl_tax')->setType(Type::getType('money'));
        $table->getColumn('row_total')->setType(Type::getType('money'));
        $table->getColumn('tax_amount')->setType(Type::getType('money'));
        $table->getColumn('price')->setType(Type::getType('money'));
        $table->getColumn('discount_amount')->setType(Type::getType('money'));
        $table->getColumn('tax_percent')->setType(Type::getType('percent'));
    }
}
