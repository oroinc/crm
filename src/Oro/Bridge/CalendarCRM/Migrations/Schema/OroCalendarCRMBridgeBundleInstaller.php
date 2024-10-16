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

    #[\Override]
    public function getMigrationVersion(): string
    {
        return 'v1_0';
    }

    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $this->addCalendarActivityAssociations($schema);
    }

    private function addCalendarActivityAssociations(Schema $schema): void
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
