<?php

namespace Oro\Bundle\CRMBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\CRMBundle\Migrations\Schema\v1_1\MigrateRelations;
use Oro\Bundle\CRMBundle\Migrations\Schema\v1_2\MigrateGridViews;
use Oro\Bundle\CRMBundle\Migrations\Schema\v1_3\EmbededFormType;
use Oro\Bundle\CRMBundle\Migrations\Schema\v1_3\TaggingEntityName;
use Oro\Bundle\CRMBundle\Migrations\Schema\v1_3_1\WorkflowItemEntityClass;
use Oro\Bundle\CRMBundle\Migrations\Schema\v1_4\NotificationEntityName;
use Oro\Bundle\CRMBundle\Migrations\Schema\v1_6\ReminderEntityName;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class OroCRMBundleInstaller implements Installation, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v4_2_0_1';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($this->container->get(ApplicationState::class)->isInstalled()) {
            MigrateRelations::updateWorkFlow($schema, $queries);
            MigrateGridViews::updateGridViews($queries);
            EmbededFormType::updateEmbededFormType($queries);
            TaggingEntityName::updateTaggingEntityName($queries);
            WorkflowItemEntityClass::updateWorkflowItemEntityClass($queries);
            NotificationEntityName::updateTaggingEntityName($queries);
            ReminderEntityName::updateRelatedEntityClassName($queries);
        }
    }
}
