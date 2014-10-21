<?php

namespace OroCRM\Bundle\TaskBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMTaskBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_task');
        $table->removeForeignKey('FK_814DEE3F11A6570A');
        $table->dropColumn('related_account_id');
        $table->removeForeignKey('FK_814DEE3F6D6C2DFA');
        $table->dropColumn('related_contact_id');
    }
}
