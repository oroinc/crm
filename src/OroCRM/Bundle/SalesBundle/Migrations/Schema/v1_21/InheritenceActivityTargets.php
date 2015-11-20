<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_21;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtension;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class InheritanceActivityTargets implements Migration, ActivityListExtensionAwareInterface
{
    /** @var ActivityListExtension */
    protected $activityListExtension;

    /**
     * {@inheritdoc}
     */
    public function setActivityListExtension(ActivityListExtension $activityListExtension)
    {
        $this->activityListExtension = $activityListExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::addInheritanceTargets($schema, $this->activityListExtension);
    }

    /**
     * @param Schema $schema
     * @param ActivityListExtension $activityListExtension
     */
    public static function addInheritanceTargets(Schema $schema, ActivityListExtension $activityListExtension)
    {
        $activityListExtension->addInheritanceTargets(
            $schema,
            'orocrm_account',
            'orocrm_sales_lead',
            ['contact', 'accounts']
        );
        $activityListExtension->addInheritanceTargets(
            $schema,
            'orocrm_account',
            'orocrm_sales_opportunity',
            ['customer', 'account']
        );
        $activityListExtension->addInheritanceTargets(
            $schema,
            'orocrm_account',
            'orocrm_sales_b2bcustomer',
            ['account']
        );
    }
}
