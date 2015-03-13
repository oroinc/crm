<?php

namespace OroCRM\Bundle\ChannelBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;

use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManager;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMChannelBundle implements Migration, ExtendExtensionAwareInterface
{
    /** @var ExtendExtension */
    protected $extendExtension;

    /**
     * {@inheritdoc}
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
        $table = $schema->getTable('oro_tracking_website');
        $this->extendExtension->addManyToOneRelation(
            $schema,
            $table,
            'channel',
            'orocrm_channel',
            'name',
            [
                ExtendOptionsManager::MODE_OPTION => ConfigModelManager::MODE_READONLY,
                'entity' => ['label' => 'orocrm.channel.entity_label'],
                'extend' => [
                    'is_extend' => true,
                    'owner'     => ExtendScope::OWNER_CUSTOM
                ],
                'datagrid' => [
                    'is_visible' => false
                ],
                'form' => [
                    'is_enabled' => true,
                    'form_type'  => 'orocrm_channel_select_type',
                    'form_options' => [
                        'tooltip'  => 'orocrm.channel.tracking_website_channel_select.tooltip'
                    ]
                ],
                'view'      => ['is_displayable' => true],
                'merge'     => ['display' => false],
                'dataaudit' => ['auditable' => false]
            ]
        );
    }
}
