<?php

namespace Oro\Bundle\ContactUsBundle\Migrations\Schema\v1_13;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityConfigBundle\Migration\RemoveFieldQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class DropChannelFieldBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_contactus_request');
        $table->removeForeignKey('FK_342872E8BDC09B73');
        $table->dropIndex('IDX_342872E8BDC09B73');
        $table->dropColumn('data_channel_id');
        $queries->addPostQuery(new RemoveFieldQuery(
            'Oro\Bundle\ContactUsBundle\Entity\ContactRequest',
            'dataChannel'
        ));
    }
}
