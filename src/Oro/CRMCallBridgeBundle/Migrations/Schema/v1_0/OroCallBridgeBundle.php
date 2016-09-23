<?php

namespace Oro\CRMCallBridgeBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;

class OroCallBridgeBundle implements Migration, ActivityExtensionAwareInterface
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
            'oro_account',
            'oro_contact',
            'oro_case',
            'oro_contactus_request',
            'oro_magento_customer',
            'oro_magento_order',
            'oro_magento_cart',
            'oro_sales_lead',
            'oro_sales_opportunity',
            'oro_sales_b2bcustomer'
        ];

        foreach ($associationTables as $tableName) {
            $associationTableName = $activityExtension->getAssociationTableName('oro_call', $tableName);
            if (!$schema->hasTable($associationTableName)) {
                $activityExtension->addActivityAssociation($schema, 'oro_call', $tableName);
            }
        }
    }
}
