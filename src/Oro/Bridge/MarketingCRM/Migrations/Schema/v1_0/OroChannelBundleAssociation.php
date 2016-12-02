<?php

namespace Oro\Bridge\MarketingCRM\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\SchemaException;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigModel;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManager;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class OroChannelBundleAssociation implements Migration, ExtendExtensionAwareInterface
{
    const TRACKING_WEBSITE_TABLE_NAME = 'oro_tracking_website';
    const CHANNEL_TABLE_NAME = 'orocrm_channel';

    /** @var ExtendExtension */
    protected $extendExtension;

    /**
     * @inheritdoc
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::addChannelForeignKeyToTrackingWebsite($schema, $this->extendExtension);
    }

    /**
     * Add 'channel' to oro_tracking_website
     *
     * @param Schema $schema
     * @param ExtendExtension $extension
     */
    public static function addChannelForeignKeyToTrackingWebsite(Schema $schema, ExtendExtension $extension)
    {
        if (!self::hasChannelAssociation($schema, $extension)) {
            $extension->addManyToOneRelation(
                $schema,
                self::TRACKING_WEBSITE_TABLE_NAME,
                'channel',
                self::CHANNEL_TABLE_NAME,
                'name',
                [
                    'entity' => ['label' => 'oro.channel.entity_label'],
                    'extend' => [
                        'is_extend' => true,
                        'owner' => ExtendScope::OWNER_CUSTOM
                    ],
                    'datagrid' => [
                        'is_visible' => DatagridScope::IS_VISIBLE_FALSE
                    ],
                    'form' => [
                        'is_enabled' => true,
                        'form_type' => 'oro_channel_select_type',
                        'form_options' => [
                            'tooltip' => 'oro.channel.tracking_website_channel_select.tooltip'
                        ]
                    ],
                    'view' => ['is_displayable' => true],
                    'merge' => ['display' => false],
                    'dataaudit' => ['auditable' => false]
                ]
            );
        }
    }

    /**
     * @param Schema $schema
     * @param ExtendExtension $extension
     *
     * @return bool
     *
     * @throws SchemaException if valid primary key does not exist
     */
    private static function hasChannelAssociation(Schema $schema, ExtendExtension $extension)
    {
        $trackingTable = $schema->getTable(self::TRACKING_WEBSITE_TABLE_NAME);
        $targetTable  = $schema->getTable(self::CHANNEL_TABLE_NAME);

        $associationName = ExtendHelper::buildAssociationName(
            $extension->getEntityClassByTableName(self::CHANNEL_TABLE_NAME)
        );

        if (!$targetTable->hasPrimaryKey()) {
            throw new SchemaException(
                sprintf('The table "%s" must have a primary key.', $targetTable->getName())
            );
        }
        $primaryKeyColumns = $targetTable->getPrimaryKey()->getColumns();
        if (count($primaryKeyColumns) !== 1) {
            throw new SchemaException(
                sprintf('A primary key of "%s" table must include only one column.', $targetTable->getName())
            );
        }

        $primaryKeyColumnName = array_pop($primaryKeyColumns);

        $nameGenerator = $extension->getNameGenerator();
        $selfColumnName = $nameGenerator->generateRelationColumnName(
            $associationName,
            '_' . $primaryKeyColumnName
        );

        return $trackingTable->hasColumn($selfColumnName);
    }
}
