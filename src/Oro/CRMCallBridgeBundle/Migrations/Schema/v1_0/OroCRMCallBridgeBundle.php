<?php

namespace Oro\CRMCallBridgeBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;

class OroCRMCallBridgeBundle implements Migration, ActivityExtensionAwareInterface
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
        self::addCallActivityRelations($schema, $this->activityExtension);
    }

    public static function addCallActivityRelations(Schema $schema, ActivityExtension $activityExtension)
    {
        $associationTables = [
            'orocrm_account',
            'orocrm_contact',
            'orocrm_case',
            'orocrm_contactus_request',
            'orocrm_magento_customer',
            'orocrm_magento_order',
            'orocrm_magento_cart',
            'orocrm_sales_lead',
            'orocrm_sales_opportunity',
            'orocrm_sales_b2bcustomer'
        ];

        foreach ($associationTables as $tableName) {
            $associationTableName = $activityExtension->getAssociationTableName('orocrm_call', $tableName);
            if (!$schema->hasTable($associationTableName)) {
                $activityExtension->addActivityAssociation($schema, 'orocrm_call', $tableName);
            }
        }
    }
}
