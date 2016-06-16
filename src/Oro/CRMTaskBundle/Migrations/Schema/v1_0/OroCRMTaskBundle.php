<?php

namespace Oro\CRMTaskBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMTaskBundle implements
    Migration,
    ActivityExtensionAwareInterface
{
     /** @var ActivityExtension */
    protected $activityExtension;

    /**
     * @param ActivityExtension $activityExtension
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
        self::addTaskActivityRelations($schema, $this->activityExtension);
    }

    /**
     * @param Schema $schema
     * @param ActivityExtension $activityExtension
     */
    public static function addTaskActivityRelations(Schema $schema, ActivityExtension $activityExtension)
    {
        if (!$schema->hasTable('orocrm_task')) {
            return;
        }
        $targetTables = [
            'orocrm_account',
            'orocrm_contact',
            'orocrm_sales_lead',
            'orocrm_sales_opportunity',
            'orocrm_sales_b2bcustomer',
            'orocrm_case',
            'orocrm_magento_customer',
            'orocrm_magento_order'
        ];
        foreach ($targetTables as $targetTable) {
            $associationTableName = $activityExtension->getAssociationTableName('orocrm_task', $targetTable);
            if (!$schema->hasTable($associationTableName)) {
                $activityExtension->addActivityAssociation($schema, 'orocrm_task', $targetTable);
            }
        }
    }
}
