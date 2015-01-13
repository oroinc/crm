<?php

namespace OroCRM\Bundle\CampaignBundle\Migrations\Schema\v1_6;

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
        $table->addUniqueIndex(['email_campaign_id', 'marketing_list_item_id'], 'orocrm_ec_litem_unq');
    }
}
