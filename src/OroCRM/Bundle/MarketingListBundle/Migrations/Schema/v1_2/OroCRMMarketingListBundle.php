<?php

namespace OroCRM\Bundle\MarketingListBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMMarketingListBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_marketing_list_item');
        $table->getColumn('last_contacted_at')->setNotnull(false);
        $table->getColumn('contacted_times')->setNotnull(false);
    }
}
