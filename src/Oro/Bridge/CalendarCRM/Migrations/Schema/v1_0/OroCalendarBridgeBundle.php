<?php

namespace Oro\Bridge\CalendarCRM\Migrations\Schema\v1_0;

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
            'oro_contact',
            'oro_case',
            'oro_account',
            'oro_magento_customer',
            'oro_magento_order',
            'oro_sales_lead',
            'oro_sales_opportunity',
            'oro_sales_b2bcustomer',
        ];

        foreach ($associationTables as $tableName) {
            $associationTableName = $activityExtension->getAssociationTableName('oro_calendar_event', $tableName);
            if (!$schema->hasTable($associationTableName)) {
                $activityExtension->addActivityAssociation($schema, 'oro_calendar_event', $tableName);
            }
        }
    }
}
