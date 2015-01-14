<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use OroCRM\Bundle\CampaignBundle\EventListener\CampaignStatisticGroupingListener;
use OroCRM\Bundle\MarketingListBundle\Datagrid\ConfigurationProvider;

class CampaignStatisticGroupingListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CampaignStatisticGroupingListener
     */
    protected $listener;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $marketingListHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupByHelper;

    protected function setUp()
    {
        $this->marketingListHelper = $this
            ->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Model\MarketingListHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupByHelper = $this
            ->getMockBuilder('Oro\Bundle\QueryDesignerBundle\Model\GroupByHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new CampaignStatisticGroupingListener($this->marketingListHelper, $this->groupByHelper);
    }

    /**
     * @dataProvider applicableDataProvider
     * @param string $gridName
     * @param bool $hasCampaign
     * @param int|null $id
     * @param bool $expected
     */
    public function testIsApplicable($gridName, $hasCampaign, $id, $expected)
    {
        $parametersBag = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $parametersBag->expects($this->once())
            ->method('has')
            ->with('emailCampaign')
            ->will($this->returnValue($hasCampaign));

        if ($hasCampaign) {
            $this->marketingListHelper->expects($this->once())
                ->method('getMarketingListIdByGridName')
                ->with($gridName)
                ->will($this->returnValue($id));
        }

        $this->assertEquals($expected, $this->listener->isApplicable($gridName, $parametersBag));
    }

    /**
     * @return array
     */
    public function applicableDataProvider()
    {
        return [
            ['test_grid', false, null, false],
            ['test_grid', true, null, false],
            ['test_grid', true, 1, true],
        ];
    }

    /**
     * @param array  $select
     * @param string $groupBy
     * @param string $expected
     *
     * @dataProvider preBuildDataProvider
     */
    public function testOnPreBuild(array $select, $groupBy, $expected)
    {
        $gridName = ConfigurationProvider::GRID_PREFIX;
        $parameters = ['emailCampaign' => 1];
        $config = DatagridConfiguration::create(
            [
                'name'   => $gridName,
                'source' => [
                    'query' => [
                        'select'  => $select,
                        'groupBy' => $groupBy
                    ]
                ]
            ]
        );

        $event = new PreBuild($config, new ParameterBag($parameters));

        $this->marketingListHelper
            ->expects($this->any())
            ->method('getMarketingListIdByGridName')
            ->with($this->equalTo($gridName))
            ->will($this->returnValue(1));
        $this->groupByHelper->expects($this->once())
            ->method('getGroupByFields')
            ->with($groupBy, $select)
            ->will($this->returnValue(explode(',', $expected)));

        $this->listener->onPreBuild($event);

        $this->assertEquals($expected, $config->offsetGetByPath(CampaignStatisticGroupingListener::PATH_GROUPBY));
    }

    public function testOnPreBuildNotApplicable()
    {
        $gridName = ConfigurationProvider::GRID_PREFIX;
        $config = DatagridConfiguration::create([]);

        $event = new PreBuild($config, new ParameterBag([]));

        $this->marketingListHelper
            ->expects($this->any())
            ->method('getMarketingListIdByGridName')
            ->with($this->equalTo($gridName));
        $this->groupByHelper->expects($this->never())
            ->method('getGroupByFields');

        $this->listener->onPreBuild($event);
    }

    /**
     * @return array
     */
    public function preBuildDataProvider()
    {
        return [
            'no fields' => [
                'selects'    => [],
                'groupBy'    => null,
                'expected'   => null,
            ],
            'group by no selects' => [
                'selects'    => [],
                'groupBy'    => 'alias.existing',
                'expected'   => 'alias.existing',
            ],
            'select no group by' => [
                'selects'    => ['alias.field'],
                'groupBy'    => null,
                'expected'   => 'alias.field',
            ],
            'select and group by' => [
                'selects'    => ['alias.field', 'alias.matchedFields as c1'],
                'groupBy'    => 'alias.existing',
                'expected'   => 'alias.existing,alias.field,c1',
            ]
        ];
    }
}
