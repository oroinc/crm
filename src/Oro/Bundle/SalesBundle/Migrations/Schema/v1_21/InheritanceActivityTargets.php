<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_21;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareInterface;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class InheritanceActivityTargets implements Migration, ActivityListExtensionAwareInterface
{
    use ActivityListExtensionAwareTrait;

    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $this->activityListExtension->addInheritanceTargets(
            $schema,
            'orocrm_account',
            'orocrm_sales_lead',
            ['contact', 'accounts']
        );
        $this->activityListExtension->addInheritanceTargets(
            $schema,
            'orocrm_account',
            'orocrm_sales_opportunity',
            ['customerAssociation', 'account']
        );
        $this->activityListExtension->addInheritanceTargets(
            $schema,
            'orocrm_account',
            'orocrm_sales_b2bcustomer',
            ['account']
        );
    }
}
