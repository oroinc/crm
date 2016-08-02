<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_24;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateLeadFirstLastName implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $leadTable = $schema->getTable('orocrm_sales_lead');

        $leadTable->getColumn('last_name')->setNotnull(false);
        $leadTable->getColumn('first_name')->setNotnull(false);
    }
}
