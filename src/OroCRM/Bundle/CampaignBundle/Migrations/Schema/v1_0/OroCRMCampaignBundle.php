<?php

namespace OroCRM\Bundle\CampaignBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMCampaignBundle implements Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::createCampaignTable($schema);
        self::setCampaignTableIndexes($schema);
    }

    public static function createCampaignTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_campaign');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 255]);
        $table->addColumn('code', 'string', ['notnull' => true, 'length' => 255]);
        $table->addColumn('start_date', 'date', ['notnull' => true]);
        $table->addColumn('end_date', 'date', ['notnull' => true]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('budget', 'float', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'IDX_55153CAD7E3C61F9', []);
    }

    public static function setCampaignTableIndexes(Schema $schema)
    {
        $table = $schema->getTable('orocrm_campaign');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_business_unit'),
            ['owner_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }
}
