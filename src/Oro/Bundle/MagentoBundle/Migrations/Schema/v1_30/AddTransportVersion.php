<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_30;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddTransportVersion implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_integration_transport');
        $table->addColumn('extension_version', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('magento_version', 'string', ['notnull' => false, 'length' => 255]);
    }
}
