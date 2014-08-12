<?php

namespace OroCRM\Bundle\CampaignBundle\Migrations\Schema\v1_2;

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
        $table = $schema->getTable('orocrm_campaign');
        $table->dropIndex('IDX_55153CAD7E3C61F9');
        $table->addIndex(['owner_id'], 'cmpgn_owner_idx', []);
    }
}
