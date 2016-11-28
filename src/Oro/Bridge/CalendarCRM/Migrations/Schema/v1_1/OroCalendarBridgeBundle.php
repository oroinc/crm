<?php

namespace Oro\Bridge\CalendarCRM\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCalendarBridgeBundle implements Migration, ActivityExtensionAwareInterface
{
    /** @var ActivityExtension */
    protected $activityExtension;

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
    public function up(Schema $schema, QueryBag $queries)
    {
        self::addCalendarActivityAssociations($schema, $this->activityExtension);
    }

    /**
     * Enable activities
     *
     * @param Schema            $schema
     * @param ActivityExtension $activityExtension
     */
    public static function addCalendarActivityAssociations(Schema $schema, ActivityExtension $activityExtension)
    {
        $associationTables = [
            'orocrm_contact',
            'orocrm_case',
            'orocrm_account',
            'orocrm_magento_customer',
            'orocrm_magento_order',
            'orocrm_sales_lead',
            'orocrm_sales_opportunity',
            'orocrm_sales_b2bcustomer',
        ];

        foreach ($associationTables as $tableName) {
            $associationTableName = $activityExtension->getAssociationTableName('oro_calendar_event', $tableName);
            if (!$schema->hasTable($associationTableName)) {
                $activityExtension->addActivityAssociation($schema, 'oro_calendar_event', $tableName);
            }
        }
    }
}
