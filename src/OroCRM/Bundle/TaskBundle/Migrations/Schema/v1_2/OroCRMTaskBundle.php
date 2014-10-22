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
        // fill empty updatedAt of orocrm_task
        $queries->addPreQuery('UPDATE orocrm_task SET updatedAt = createdAt WHERE updatedAt IS NULL');

        $taskTable = $schema->getTable('orocrm_task');

        // make updatedAt NOT NULL
        $taskTable->getColumn('updatedAt')->setOptions(['notnull' => true]);

        // relation with account
        $taskTable->removeForeignKey('FK_814DEE3F11A6570A');
        $taskTable->dropColumn('related_account_id');

        // relation with contact
        $taskTable->removeForeignKey('FK_814DEE3F6D6C2DFA');
        $taskTable->dropColumn('related_contact_id');
    }
}
