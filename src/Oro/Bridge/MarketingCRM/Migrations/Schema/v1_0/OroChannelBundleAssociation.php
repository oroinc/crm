<?php

namespace Oro\Bridge\MarketingCRM\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ChannelBundle\Form\Type\ChannelSelectType;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroChannelBundleAssociation implements
    Migration,
    ExtendExtensionAwareInterface
{
    const TRACKING_WEBSITE_TABLE_NAME = 'oro_tracking_website';
    const CHANNEL_TABLE_NAME = 'orocrm_channel';
    const CHANNEL_TABLE_FK_NAME = 'channel_id';

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
        if (!self::hasChannelAssociation($schema)) {
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
                        'form_type' => ChannelSelectType::class,
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
     *
     * @return bool
     */
    private static function hasChannelAssociation(Schema $schema)
    {
        $trackingTable = $schema->getTable(self::TRACKING_WEBSITE_TABLE_NAME);

        return $trackingTable->hasColumn(self::CHANNEL_TABLE_FK_NAME);
    }
}
