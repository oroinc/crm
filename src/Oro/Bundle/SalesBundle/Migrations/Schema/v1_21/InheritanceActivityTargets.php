<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_21;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtension;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareInterface;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class InheritanceActivityTargets implements Migration, ActivityListExtensionAwareInterface
{
    use ActivityListExtensionAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::addInheritanceTargets($schema, $this->activityListExtension);
    }

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
            ['customerAssociation', 'account']
        );
        $activityListExtension->addInheritanceTargets(
            $schema,
            'orocrm_account',
            'orocrm_sales_b2bcustomer',
            ['account']
        );
    }
}
