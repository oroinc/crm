<?php

namespace Oro\Bundle\ActivityContactBundle\Tests\Unit\Model;

use Oro\Bundle\ActivityContactBundle\Model\TargetExcludeList;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\CallBundle\Entity\Call;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\TaskBundle\Entity\Task;
use Oro\Bundle\UserBundle\Entity\User;

class TargetExcludeListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return array
     */
    public function isExcludedDataProvider()
    {
        return [
            [User::class, true],
            [Task::class, true],
            [CalendarEvent::class, true],
            [Call::class, true],
            [Email::class, true],
            [\DateTime::class, false],
        ];
    }

    /**
     * @param string $className
     * @param bool $isExcluded
     * @dataProvider isExcludedDataProvider
     */
    public function testIsExcluded($className, $isExcluded)
    {
        $this->assertEquals($isExcluded, TargetExcludeList::isExcluded($className));
    }
}
