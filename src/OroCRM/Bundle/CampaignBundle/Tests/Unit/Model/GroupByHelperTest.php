<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Model;

use OroCRM\Bundle\CampaignBundle\Model\GroupByHelper;

class GroupByHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider groupByDataProvider
     * @param array $selects
     * @param string $groupBy
     * @param array $expected
     */
    public function testGetGroupByFields($selects, $groupBy, $expected)
    {
        $helper = new GroupByHelper();
        $this->assertEquals($expected, $helper->getGroupByFields($groupBy, $selects));
    }

    /**
     * @return array
     */
    public function groupByDataProvider()
    {
        return [
            'no fields' => [
                'selects'    => [],
                'groupBy'    => null,
                'expected'   => [],
            ],
            'group by no fields' => [
                'selects'    => [],
                'groupBy'    => 'alias.existing',
                'expected'   => ['alias.existing'],
            ],
            'field without alias' => [
                'selects'    => ['alias.field'],
                'groupBy'    => null,
                'expected'   => ['alias.field'],
            ],
            'aliases and without' => [
                'selects'    => ['alias.field', 'alias.matchedFields  as  c1', 'alias.secondMatched aS someAlias3'],
                'groupBy'    => null,
                'expected'   => ['alias.field', 'c1', 'someAlias3'],
            ],
            'mixed fields and group by' => [
                'selects'    => ['alias.field', 'alias.matchedFields as c1'],
                'groupBy'    => 'alias.existing',
                'expected'   => ['alias.existing', 'alias.field', 'c1'],
            ],
            'wrong field definition' => [
                'selects'    => ['alias.matchedFields wrongas c1'],
                'groupBy'    => null,
                'expected'   => [],
            ],
            'with aggregate' => [
                'selects'    => ['MAX(t1.f0)', 'AvG(t10.F19) as agF1', 'alias.matchedFields AS c1'],
                'groupBy'    => null,
                'expected'   => ['c1'],
            ],
        ];
    }
}
