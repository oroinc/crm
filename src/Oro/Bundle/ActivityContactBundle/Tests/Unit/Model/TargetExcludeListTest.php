<?php

namespace Oro\Bundle\ActivityContactBundle\Tests\Unit\Model;

use Oro\Bundle\ActivityContactBundle\Model\TargetExcludeList;
use Oro\Bundle\TestFrameworkBundle\Entity\TestActivity;
use Oro\Bundle\UserBundle\Entity\User;

class TargetExcludeListTest extends \PHPUnit\Framework\TestCase
{
    public function isExcludedDataProvider(): array
    {
        return [
            [User::class, true],
            [TestActivity::class, true],
            [\DateTime::class, false],
        ];
    }

    /**
     * @dataProvider isExcludedDataProvider
     */
    public function testIsExcluded(string $className, bool $isExcluded): void
    {
        $this->assertEquals($isExcluded, TargetExcludeList::isExcluded($className));
    }
}
