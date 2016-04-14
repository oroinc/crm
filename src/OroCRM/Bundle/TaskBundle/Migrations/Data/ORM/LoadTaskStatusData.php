<?php

namespace OroCRM\Bundle\TaskBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Migration\Fixture\AbstractEnumFixture;

class LoadTaskStatusData extends AbstractEnumFixture
{
    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    protected function getEnumCode()
    {
        return 'task_status';
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultValue()
    {
        return 'open';
    }
}
