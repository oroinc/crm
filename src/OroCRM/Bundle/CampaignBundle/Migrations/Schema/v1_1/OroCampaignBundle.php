<?php

namespace Oro\Bundle\CampaignBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\CampaignBundle\Entity\Campaign;

class OroCampaignBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_campaign');
        $table->addColumn('report_period', 'string', ['length' => 25]);

        $queries->addPostQuery(
            sprintf(
                "UPDATE oro_campaign SET report_period = '%s'",
                Campaign::PERIOD_DAILY
            )
        );
    }
}
