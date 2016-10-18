<?php

namespace Oro\Bundle\ContactUsBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroContactUsBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_contactus_request');
        $table->removeForeignKey('FK_342872E872F5A1AA');
        $table->dropIndex('IDX_342872E872F5A1AA');
        $table->dropColumn('channel_id');
    }
}
