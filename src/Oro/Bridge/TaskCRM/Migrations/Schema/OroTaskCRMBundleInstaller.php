<?php

namespace Oro\Bridge\TaskCRM\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroTaskCRMBundleInstaller implements
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
        $this->addTaskActivityRelations($schema);
    }

    private function addTaskActivityRelations(Schema $schema): void
    {
        $targetTables = [
            'orocrm_account',
            'orocrm_contact',
            'orocrm_sales_lead',
            'orocrm_sales_opportunity',
            'orocrm_sales_b2bcustomer',
            'orocrm_case',
        ];
        foreach ($targetTables as $targetTable) {
            $associationTableName = $this->activityExtension->getAssociationTableName('orocrm_task', $targetTable);
            if (!$schema->hasTable($associationTableName)) {
                $this->activityExtension->addActivityAssociation($schema, 'orocrm_task', $targetTable);
            }
        }
    }
}
