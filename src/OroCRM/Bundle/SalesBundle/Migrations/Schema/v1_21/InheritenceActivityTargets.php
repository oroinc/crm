<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_21;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class InheritanceActivityTargets implements Migration, ActivityExtensionAwareInterface
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
        self::addInheritanceTargets($schema, $this->activityExtension);
    }

    /**
     * @param Schema $schema
     * @param ActivityExtension $activityExtension
     */
    public static function addInheritanceTargets(Schema $schema, ActivityExtension $activityExtension)
    {
        $activityExtension->addInheritanceTargets($schema, 'orocrm_account', 'orocrm_sales_lead');
        $activityExtension->addInheritanceTargets($schema, 'orocrm_account', 'orocrm_sales_opportunity');
        $activityExtension->addInheritanceTargets($schema, 'orocrm_account', 'orocrm_sales_b2bcustomer');
    }
}
