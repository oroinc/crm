<?php

namespace OroCRM\Bundle\CaseBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMCaseBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_email_mailbox_processor');

        $table->addColumn('case_assign_to_id', 'integer', array('notnull' => false));
        $table->addColumn('case_owner_id', 'integer', array('notnull' => false));
        $table->addColumn('case_status_name', 'string', array('notnull' => false, 'length' => 16));
        $table->addColumn('case_priority_name', 'string', array('notnull' => false, 'length' => 16));
    }
}
