<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_40;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class CreateActivityAssociation implements Migration, ActivityExtensionAwareInterface
{
    /** @var ActivityExtension */
    protected $activityExtension;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->addNoteAssociations($schema);
    }

    /**
     * Enable notes for Magento Order entity
     *
     * @param Schema $schema
     */
    protected function addNoteAssociations(Schema $schema)
    {
        $associationTableName = $this->activityExtension->getAssociationTableName('oro_note', 'orocrm_magento_order');
        if (!$schema->hasTable($associationTableName)) {
            $this->activityExtension->addActivityAssociation($schema, 'oro_note', 'orocrm_magento_order');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }
}
