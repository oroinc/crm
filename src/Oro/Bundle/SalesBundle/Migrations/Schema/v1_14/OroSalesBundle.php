<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_14;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroSalesBundle implements Migration, ActivityExtensionAwareInterface
{
    use ActivityExtensionAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        if ($schema->hasTable('orocrm_task')) {
            $this->activityExtension->addActivityAssociation($schema, 'orocrm_task', 'orocrm_sales_lead');
            $this->activityExtension->addActivityAssociation($schema, 'orocrm_task', 'orocrm_sales_opportunity');
            $this->activityExtension->addActivityAssociation($schema, 'orocrm_task', 'orocrm_sales_b2bcustomer');
        }
    }
}
