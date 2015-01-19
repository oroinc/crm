<?php

namespace OroCRM\Bundle\CampaignBundle\Migrations\Schema\v1_8;

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
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addIndex(['owner_id'], 'idx_3ce99ef07e3c61f9', []);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['owner_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addIndex(['organization_id'], 'idx_3ce99ef032c8a3de', []);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
    }
}
