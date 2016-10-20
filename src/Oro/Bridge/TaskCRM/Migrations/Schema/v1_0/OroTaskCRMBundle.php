<?php

namespace Oro\Bridge\TaskCRM\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroTaskCRMBundle implements
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
        if (!$schema->hasTable('oro_task')) {
            return;
        }
        $targetTables = [
            'oro_account',
            'oro_contact',
            'oro_sales_lead',
            'oro_sales_opportunity',
            'oro_sales_b2bcustomer',
            'oro_case',
            'oro_magento_customer',
            'oro_magento_order'
        ];
        foreach ($targetTables as $targetTable) {
            $associationTableName = $activityExtension->getAssociationTableName('oro_task', $targetTable);
            if (!$schema->hasTable($associationTableName)) {
                $activityExtension->addActivityAssociation($schema, 'oro_task', $targetTable);
            }
        }
    }
}
