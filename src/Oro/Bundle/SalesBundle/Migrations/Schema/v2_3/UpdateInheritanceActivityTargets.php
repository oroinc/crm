<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v2_3;

use Doctrine\DBAL\Schema\Schema;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigEntityValueQuery;
use Oro\Bundle\ActivityListBundle\Helper\ActivityInheritanceTargetsHelper;

class UpdateInheritanceActivityTargets implements Migration, OrderedMigrationInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var ContainerInterface */
    protected $container;

    public function getOrder()
    {
        return 4;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** @var ActivityInheritanceTargetsHelper $inheritanceTargetsHelper */
        $inheritanceTargetsHelper = $this->getInheritanceTargetHelper();
        $newValue = $inheritanceTargetsHelper->removeInheritanceTargetClass(Account::class, Opportunity::class);
        $queries->addQuery(
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
