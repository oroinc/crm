<?php

namespace OroCRM\Bundle\TaskBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Migration\Fixture\AbstractEnumFixture;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use OroCRM\Bundle\TaskBundle\Migrations\Schema\v1_10\UpdateTaskStatusQuery;

class LoadTaskStatusData extends AbstractEnumFixture
{
    /**
     * {@inheritdoc}
     */
    protected function getData()
    {
        return [
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'closed' => 'Closed'
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getEnumCode()
    {
        return 'task_status';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultValue()
    {
        return 'open';
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        parent::load($manager);

        $className = ExtendHelper::buildEnumValueClassName($this->getEnumCode());
        $enumRepo = $manager->getRepository($className);
        $connection = $repository->getEntityManager()->getConnection();

        $logger = new ArrayLogger();
        $query = new UpdateTaskStatusQuery();
        $query->setConnection($connection);
        $query->execute($logger);
    }
}
