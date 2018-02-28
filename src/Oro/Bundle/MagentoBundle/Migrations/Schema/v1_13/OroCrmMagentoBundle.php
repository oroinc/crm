<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_13;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCrmMagentoBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_magento_customer_addr');
        $table->addColumn('phone', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('related_contact_phone_id', 'integer', ['notnull' => false]);
        $table->addUniqueIndex(['related_contact_phone_id'], 'UNIQ_1E239D64E3694F65');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact_phone'),
            ['related_contact_phone_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
