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
        $taskTable = $schema->getTable('orocrm_task');

        // relation with account
        $taskTable->removeForeignKey('FK_814DEE3F11A6570A');
        $taskTable->dropColumn('related_account_id');

        // relation with contact
        $taskTable->removeForeignKey('FK_814DEE3F6D6C2DFA');
        $taskTable->dropColumn('related_contact_id');
    }
}
