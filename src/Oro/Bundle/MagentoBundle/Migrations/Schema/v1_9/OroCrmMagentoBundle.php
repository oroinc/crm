<?php
namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_9;

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
        $table->addColumn('related_contact_address_id', 'integer', ['notnull' => false]);
        $table->addUniqueIndex(['related_contact_address_id'], 'UNIQ_1E239D648137CB7B');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact_address'),
            ['related_contact_address_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
