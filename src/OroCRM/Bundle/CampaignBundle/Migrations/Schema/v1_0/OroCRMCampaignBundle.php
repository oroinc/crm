<?php

namespace OroCRM\Bundle\CampaignBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMCampaignBundle implements Migration, ExtendExtensionAwareInterface
{
    /**
     * @var ExtendExtension
     */
    protected $extendExtension;

    /**
     * @inheritdoc
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->createTable('orocrm_campaign');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 255]);
        $table->addColumn('code', 'string', ['notnull' => true, 'length' => 255]);
        $table->addColumn('combined_name', 'string', ['length' => 255]);
        $table->addColumn('start_date', 'date', ['notnull' => false]);
        $table->addColumn('end_date', 'date', ['notnull' => false]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('budget', 'money', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code'], 'UNIQ_E9A0640377153098');
        $table->addIndex(['owner_id'], 'IDX_55153CAD7E3C61F9', []);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_E9A064037E3C61F9'
        );

        $this->extendExtension->addManyToOneRelation(
            $schema,
            'orocrm_sales_lead',
            'campaign',
            'orocrm_campaign',
            'combined_name',
            ['extend' => ['owner' => 'Custom', 'is_extend' => true]]
        );
    }
}
