<?php

namespace OroCRM\Bundle\CampaignBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\SecurityBundle\Migrations\Schema\UpdateOwnershipTypeQuery;

class OroCRMCampaignBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::addOrganization($schema, 'orocrm_campaign');
        self::addOrganization($schema, 'orocrm_campaign_email');

        //Add organization fields to ownership entity config
        $queries->addQuery(
            new UpdateOwnershipTypeQuery(
                'OroCRM\Bundle\CampaignBundle\Entity\Campaign',
                [
                    'organization_field_name'  => 'organization',
                    'organization_column_name' => 'organization_id'
                ]
            )
        );
        $queries->addQuery(
            new UpdateOwnershipTypeQuery(
                'OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign',
                [
                    'organization_field_name'  => 'organization',
                    'organization_column_name' => 'organization_id'
                ]
            )
        );
    }

    /**
     * Adds organization_id field
     *
     * @param Schema $schema
     * @param string $tableName
     */
    public static function addOrganization(Schema $schema, $tableName)
    {
        $table = $schema->getTable($tableName);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
