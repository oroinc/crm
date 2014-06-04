<?php

namespace OroCRM\Bundle\CallBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Schema\ExtendColumn;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMCallBundle implements Migration, ExtendExtensionAwareInterface
{
    /** @var  ExtendExtension */
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
        /**
         * Enable notes for Call entity
         */
        $noteTable = $schema->getTable('oro_note');
        $callTable = $schema->getTable('orocrm_call');

        $callOptions['note']['enabled'] = true;
        $callTable->addOption(ExtendColumn::ORO_OPTIONS_NAME, $callOptions);

        $callAssociationName = ExtendHelper::buildAssociationName(
            $this->extendExtension->getEntityClassByTableName('orocrm_call')
        );

        $this->extendExtension->addManyToOneRelation(
            $schema,
            $noteTable,
            $callAssociationName,
            $callTable,
            'subject',
            ['extend' => ['owner' => 'Custom', 'is_extend' => true]]
        );
    }
}
