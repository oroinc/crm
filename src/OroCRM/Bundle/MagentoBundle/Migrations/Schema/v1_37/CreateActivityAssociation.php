<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_37;

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
        self::addActivityListAssociationTables($schema, $this->activityListExtension);
    }

    /**
     * @param Schema $schema
     */
    public static function enableActivityAssociations(Schema $schema)
    {
        $options = new OroOptions();
        $options->set('activity', 'immutable', false);

        $schema->getTable('orocrm_magento_cart')->addOption(OroOptions::KEY, $options);
        $schema->getTable('orocrm_magento_order')->addOption(OroOptions::KEY, $options);
    }

    /**
     * @param Schema            $schema
     * @param ActivityExtension $activityExtension
     */
    public static function addActivityAssociations(Schema $schema, ActivityExtension $activityExtension)
    {
        $activityExtension->addActivityAssociation($schema, 'oro_email', 'orocrm_magento_order');
        $activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_magento_order');

        $activityExtension->addActivityAssociation($schema, 'oro_email', 'orocrm_magento_cart');
        $activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_magento_cart');
    }

    /**
     * Manually create activitylist association tables for further filling.
     *
     * @param Schema                $schema
     * @param ActivityListExtension $activityListExtension
     */
    public static function addActivityListAssociationTables(
        Schema $schema,
        ActivityListExtension $activityListExtension
    ) {
        $activityListExtension->addActivityListAssociation($schema, 'orocrm_magento_cart');
        $activityListExtension->addActivityListAssociation($schema, 'orocrm_magento_order');
    }
}
