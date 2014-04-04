<?php
namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_6;

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
        $integrationTransport = $schema->getTable('oro_integration_transport');
        $integrationTransport->addColumn('admin_url', 'string', ['notnull' => false, 'length' => 255]);

        $orderTable = $schema->getTable('orocrm_magento_order');
        $orderTable->dropIndex('UNIQ_4D09F3051AD5CDBF');
        $orderTable->addIndex(['cart_id'], 'IDX_4D09F3051AD5CDBF');
    }
}
