<?php

namespace OroCRM\Bundle\TaskBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Migration\Fixture\AbstractEnumFixture;

class LoadTaskStatusData extends AbstractEnumFixture
{
    /**
     * Returns an array of possible enum values, where array key is an id and array value is an English translation
     *
     * @return array
     */
    protected function getData()
    {
        return [
            'Open' => true,
            'In Progress' => false,
            'Closed' => false
        ];
    }

    /**
     * Returns an enum code of an extend entity
     *
     * @return string
     */
    protected function getEnumCode()
    {
        return 'task_status';
    }
}
