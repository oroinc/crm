<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v2_3;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtension;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;

class AddInheritanceActivityTargets implements Migration, ActivityListExtensionAwareInterface
{
    /** @var ActivityListExtension */
    protected $activityListExtension;

    public function getOrder()
    {
        return 5;
    }

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
        if (!$schema->hasTable('orocrm_sales_b2bcustomer') || !$schema->hasTable('orocrm_account')) {
          return;
        }

        $b2bCustomerPath = [
            'join'          => 'Oro\Bundle\SalesBundle\Entity\Customer',
            'conditionType' => 'WITH',
            'field'         => AccountCustomerManager::getCustomerTargetField(
                'Oro\Bundle\SalesBundle\Entity\B2bCustomer'
            ),
        ];

        $this->activityListExtension->addInheritanceTargets(
            $schema,
            'orocrm_account',
            'orocrm_sales_b2bcustomer',
            [$b2bCustomerPath, 'account']
        );

        $this->activityListExtension->addInheritanceTargets(
            $schema,
            'orocrm_account',
            'orocrm_sales_opportunity',
            [$b2bCustomerPath, 'account']
        );
    }
}
