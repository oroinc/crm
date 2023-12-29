<?php

namespace Oro\Bridge\CalendarCRM\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCalendarCRMBridgeBundleInstaller implements
    Installation,
    ActivityExtensionAwareInterface
{
    use ActivityExtensionAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->addCalendarActivityAssociations($schema);
    }

    private function addCalendarActivityAssociations(Schema $schema)
    {
        $associationTables = [
            'orocrm_contact',
            'orocrm_case',
            'orocrm_account',
            'orocrm_sales_lead',
            'orocrm_sales_opportunity',
            'orocrm_sales_b2bcustomer',
        ];

        foreach ($associationTables as $tableName) {
            $associationTableName = $this->activityExtension->getAssociationTableName(
                'oro_calendar_event',
                $tableName
            );
            if (!$schema->hasTable($associationTableName)) {
                $this->activityExtension->addActivityAssociation($schema, 'oro_calendar_event', $tableName);
            }
        }
    }
}
