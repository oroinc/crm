<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_14;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;

class OroCRMSalesBundle implements Migration, ActivityExtensionAwareInterface
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
        self::addActivityAssociations($schema, $this->activityExtension);
    }

    /**
     * Enables Email activity for Lead and Opportunity entities
     *
     * @param Schema            $schema
     * @param ActivityExtension $activityExtension
     */
    public static function addActivityAssociations(Schema $schema, ActivityExtension $activityExtension)
    {
        if ($schema->hasTable('orocrm_task')) {
            $activityExtension->addActivityAssociation($schema, 'orocrm_task', 'orocrm_sales_lead');
            $activityExtension->addActivityAssociation($schema, 'orocrm_task', 'orocrm_sales_opportunity');
            $activityExtension->addActivityAssociation($schema, 'orocrm_task', 'orocrm_sales_b2bcustomer');
        }
    }
}
