<?php

namespace OroCRM\Bundle\CampaignBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMCampaignBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_campaign_email_stats');
        $table->addColumn('open_count', 'integer', ['notnull' => false]);
        $table->addColumn('click_count', 'integer', ['notnull' => false]);
        $table->addColumn('bounce_count', 'integer', ['notnull' => false]);
        $table->addColumn('abuse_count', 'integer', ['notnull' => false]);
        $table->addColumn('unsubscribe_count', 'integer', ['notnull' => false]);

        $queries->addQuery(new AggregateStatisticsQuery());
    }
}
