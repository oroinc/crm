<?php

namespace OroCRM\Bundle\ContactUsBundle\Migrations\Schema\v1_10;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;

use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtension;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareInterface;

class CreateActivityAssociation implements
    Migration,
    OrderedMigrationInterface,
    ActivityExtensionAwareInterface,
    ActivityListExtensionAwareInterface
{
    /** @var ActivityExtension */
    protected $activityExtension;

    /** @var ActivityListExtension */
    protected $activityListExtension;

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function setActivityListExtension(ActivityListExtension $activityListExtension)
    {
        $this->activityListExtension = $activityListExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::enableActivityAssociations($schema);
        self::addActivityAssociations($schema, $this->activityExtension);
        self::addActivityListAssociationTable($schema, $this->activityListExtension);
    }

    /**
     * @param Schema $schema
     */
    public static function enableActivityAssociations(Schema $schema)
    {
        $options = new OroOptions();
        $options->set('activity', 'immutable', false);

        $schema->getTable('orocrm_contactus_request')->addOption(OroOptions::KEY, $options);
    }

    /**
     * @param Schema            $schema
     * @param ActivityExtension $activityExtension
     */
    public static function addActivityAssociations(Schema $schema, ActivityExtension $activityExtension)
    {
        $activityExtension->addActivityAssociation($schema, 'oro_email', 'orocrm_contactus_request');
    }

    /**
     * Manually create activitylist association table for further filling.
     *
     * @param Schema                $schema
     * @param ActivityListExtension $activityListExtension
     */
    public static function addActivityListAssociationTable(
        Schema $schema,
        ActivityListExtension $activityListExtension
    ) {
        $activityListExtension->addActivityListAssociation($schema, 'orocrm_contactus_request');
    }
}
