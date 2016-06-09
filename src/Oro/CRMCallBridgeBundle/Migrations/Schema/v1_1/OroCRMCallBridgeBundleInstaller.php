<?php

namespace Oro\CRMCallBridgeBundle\Migrations\Schema\v1_1;

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

        $cartCall = $schema->getTable('orocrm_magento_cart_calls');
        $cartCall->removeForeignKey('FK_83A8477550A89B2C');
        $cartCall->addForeignKeyConstraint(
            $schema->getTable('orocrm_call'),
            ['call_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $cartCall->removeForeignKey('FK_83A847751AD5CDBF');
        $cartCall->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_cart'),
            ['cart_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $orderCall = $schema->getTable('orocrm_magento_order_calls');
        $orderCall->removeForeignKey('FK_A885A3450A89B2C');
        $orderCall->addForeignKeyConstraint(
            $schema->getTable('orocrm_call'),
            ['call_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $orderCall->removeForeignKey('FK_A885A348D9F6D38');
        $orderCall->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_order'),
            ['order_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
    }
}