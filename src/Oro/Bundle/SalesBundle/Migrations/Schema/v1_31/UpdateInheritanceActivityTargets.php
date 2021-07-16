<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_31;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ActivityListBundle\Helper\ActivityInheritanceTargetsHelper;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtension;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareInterface;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigEntityValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class UpdateInheritanceActivityTargets implements
    ActivityListExtensionAwareInterface,
    ContainerAwareInterface,
    Migration,
    OrderedMigrationInterface
{
    use ContainerAwareTrait;

    /** @var ActivityListExtension */
    protected $activityListExtension;

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 4;
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
        $this->removeOldInheritanceActivityTargets($queries);
        $this->activityListExtension->addInheritanceTargets(
            $schema,
            'orocrm_account',
            'orocrm_sales_opportunity',
            ['customerAssociation', 'account']
        );
    }

    protected function removeOldInheritanceActivityTargets(QueryBag $queries)
    {
        $inheritanceTargets = $this
            ->getInheritanceTargetHelper()
            ->getInheritanceTargets(Account::class);

        $newValue = array_filter(
            $inheritanceTargets,
            function ($inheritanceTarget) {
                return $inheritanceTarget['target'] !== Opportunity::class;
            }
        );

        $queries->addPreQuery(
            new UpdateEntityConfigEntityValueQuery(
                Account::class,
                'activity',
                'inheritance_targets',
                $newValue
            )
        );
    }

    /**
     * @return ActivityInheritanceTargetsHelper
     */
    protected function getInheritanceTargetHelper()
    {
        return $this->container->get('oro_activity_list.helper.activity_inheritance_targets');
    }
}
