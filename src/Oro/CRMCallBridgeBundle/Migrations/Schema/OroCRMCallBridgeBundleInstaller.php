<?php

namespace Oro\CRMCallBridgeBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;

use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class OroCRMCallBridgeBundleInstaller implements
    Installation,
    ActivityExtensionAwareInterface
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
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    public function up(Schema $schema, QueryBag $queries)
    {
        /** if CallBundle is installed  do nothing **/
        if ($schema->hasTable('orocrm_call')) {
            return;
        }

        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_sales_lead');
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_sales_opportunity');
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_sales_b2bcustomer');
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_contactus_request');
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_magento_customer');
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_magento_order');
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_magento_cart');

        $associationTableName = $this->activityExtension->getAssociationTableName('orocrm_call', 'orocrm_case');
        if (!$schema->hasTable($associationTableName)) {
            $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_case');
        }
    }

   
}
