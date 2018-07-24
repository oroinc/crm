<?php

namespace Oro\Bundle\ActivityContactBundle\Tests\Unit\Model;

use Oro\Bundle\ActivityContactBundle\Model\TargetExcludeList;

class TargetExcludeListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return array
     */
    public function isExcludedDataProvider()
    {
        return [
            ['Oro\Bundle\UserBundle\Entity\User', true],
            ['Oro\Bundle\TaskBundle\Entity\Task', true],
            ['Oro\Bundle\CalendarBundle\Entity\CalendarEvent', true],
            ['Oro\Bundle\CallBundle\Entity\Call', true],
            ['Oro\Bundle\EmailBundle\Entity\Email', true],
            ['DateTime', false],
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
