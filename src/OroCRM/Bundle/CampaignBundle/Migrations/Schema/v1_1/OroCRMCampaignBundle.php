<?php

namespace OroCRM\Bundle\CampaignBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use OroCRM\Bundle\CampaignBundle\Entity\Campaign;

class OroCRMCampaignBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_campaign');
        $table->addColumn('report_period', 'string', ['length' => 25]);

        $queries->addPostQuery(
            sprintf(
                "UPDATE orocrm_campaign SET report_period = '%s'",
                Campaign::PERIOD_DAILY
            )
        );
    }
}
