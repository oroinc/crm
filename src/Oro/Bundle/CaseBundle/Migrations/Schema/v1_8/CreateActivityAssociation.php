<?php

namespace Oro\Bundle\CaseBundle\Migrations\Schema\v1_8;

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
        $this->addActivityAssociations($schema);
    }

    /**
     * Enable activities
     */
    protected function addActivityAssociations(Schema $schema)
    {
        $associationTableName = $this->activityExtension->getAssociationTableName('oro_call', 'orocrm_case');
        if (!$schema->hasTable($associationTableName)) {
            $this->activityExtension->addActivityAssociation($schema, 'oro_call', 'orocrm_case');
        }

        $associationTableName = $this->activityExtension->getAssociationTableName('oro_note', 'orocrm_case');
        if (!$schema->hasTable($associationTableName)) {
            $this->activityExtension->addActivityAssociation($schema, 'oro_note', 'orocrm_case');
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
