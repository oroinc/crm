<?php

namespace Oro\Bridge\CallCRM\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCallCRMBridgeBundleInstaller implements
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
        $this->addCallActivityRelations($schema);
    }

    private function addCallActivityRelations(Schema $schema): void
    {
        $associationTables = [
            'orocrm_account',
            'orocrm_contact',
            'orocrm_case',
            'orocrm_contactus_request',
            'orocrm_sales_lead',
            'orocrm_sales_opportunity',
            'orocrm_sales_b2bcustomer'
        ];
        foreach ($associationTables as $tableName) {
            $associationTableName = $this->activityExtension->getAssociationTableName(
                'orocrm_call',
                $tableName
            );
            if (!$schema->hasTable($associationTableName)) {
                $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', $tableName);
            }
        }
    }
}
