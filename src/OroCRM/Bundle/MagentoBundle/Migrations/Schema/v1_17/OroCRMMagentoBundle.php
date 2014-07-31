<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_17;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMMagentoBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $tables = [
            'orocrm_magento_cart'           => 'FK_96661A8072F5A1AA',
            'orocrm_magento_customer'       => 'FK_2A61EE7D72F5A1AA',
            'orocrm_magento_customer_group' => 'FK_71E09CA872F5A1AA',
            'orocrm_magento_order'          => 'FK_4D09F30572F5A1AA',
            'orocrm_magento_product'        => 'FK_5A17298272F5A1AA',
            'orocrm_magento_store'          => 'FK_477738EA72F5A1AA',
            'orocrm_magento_website'        => 'FK_CE3270C872F5A1AA',
        ];

        foreach ($tables as $table => $foreignKey) {
            $queries->addPreQuery(
                sprintf('ALTER TABLE %s DROP FOREIGN KEY %s;', $table, $foreignKey)
            );

            $table = $schema->getTable($table);
            $table->getColumn('channel_id')->setType(Type::getType('integer'));
            $table->addForeignKeyConstraint(
                $schema->getTable('oro_integration_channel'),
                ['channel_id'],
                ['id'],
                ['onDelete' => 'SET NULL', 'onUpdate' => null]
            );
        }

        $table = $schema->getTable('orocrm_magento_customer');
        $table->getColumn('vat')->setType(Type::getType('decimal'));
        $table->dropIndex('unq_origin_id_channel_id');
        $table->addUniqueIndex(['origin_id', 'channel_id'], 'magecustomer_oid_cid_unq');
    }
}
