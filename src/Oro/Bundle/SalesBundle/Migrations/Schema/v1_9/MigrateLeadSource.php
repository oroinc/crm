<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_9;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigModel;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MigrateLeadSource implements Migration, ExtendExtensionAwareInterface
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
        $this->extendExtension->addEnumField(
            $schema,
            'orocrm_sales_lead',
            'source',
            'lead_source'
        );

        $queries->addPostQuery(
            sprintf(
                'UPDATE oro_entity_config_field SET mode=\'%s\' WHERE field_name=\'%s\'',
                ConfigModel::MODE_HIDDEN,
                'extend_source'
            )
        );
    }
}
