<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_51;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddSharedGuestEmailListField implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_integration_transport');
        $table->addColumn(
            'shared_guest_email_list',
            'simple_array',
            ['notnull' => false, 'comment' => '(DC2Type:simple_array)']
        );
    }
}
