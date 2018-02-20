<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_50_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class SetCascadeDeleteToOrderCartFK implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table  = $schema->getTable('orocrm_magento_order');
        $fkName = 'FK_4D09F3051AD5CDBF';
        $fk     = $table->getForeignKey($fkName);
        if ($fk->getOption('onDelete') !== 'CASCADE') {
            $table->removeForeignKey($fkName);
            $table->addForeignKeyConstraint(
                $schema->getTable('orocrm_magento_cart'),
                ['cart_id'],
                ['id'],
                ['onDelete' => 'CASCADE']
            );
        }
    }
}
