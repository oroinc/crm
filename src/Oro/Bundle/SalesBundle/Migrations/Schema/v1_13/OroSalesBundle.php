<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_13;

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
        $this->activityExtension->addActivityAssociation($schema, 'oro_call', 'orocrm_sales_lead');
        $this->activityExtension->addActivityAssociation($schema, 'oro_call', 'orocrm_sales_opportunity');
        $this->activityExtension->addActivityAssociation($schema, 'oro_call', 'orocrm_sales_b2bcustomer');
    }
}
