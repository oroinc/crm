<?php

namespace OroCRM\Bundle\CampaignBundle\Migrations\Schema\v1_2;

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
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addIndex(['organization_id'], 'IDX_E9A0640332C8A3DE', []);
        $table->addForeignKeyConstraint($schema->getTable('oro_organization'), ['organization_id'],
            ['id'], ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
