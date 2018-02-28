<?php
namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_7;

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

        $cart = $schema->getTable('orocrm_magento_cart');
        $cart->addColumn('status_message', 'string', ['notnull' => false, 'length' => 255]);
    }
}
