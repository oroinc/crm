<?php

namespace Oro\Bundle\CaseBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCaseBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        // fill empty updatedAt of orocrm_case_comment
        $queries->addPreQuery('UPDATE orocrm_case_comment SET updatedAt = createdAt WHERE updatedAt IS NULL');

        // make updatedAt NOT NULL
        $table = $schema->getTable('orocrm_case_comment');
        $table->changeColumn('updatedAt', ['notnull' => true]);
    }
}
