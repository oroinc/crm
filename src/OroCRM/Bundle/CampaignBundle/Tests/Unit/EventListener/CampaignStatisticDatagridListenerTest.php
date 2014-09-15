<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use OroCRM\Bundle\CampaignBundle\EventListener\CampaignStatisticDatagridListener;
use OroCRM\Bundle\MarketingListBundle\Datagrid\MarketingListItemsListener;

class CampaignStatisticDatagridListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CampaignStatisticDatagridListener
     */
    protected $listener;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $segmentHelper;

    protected function setUp()
    {
        $this->segmentHelper = $this
            ->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Model\MarketingListSegmentHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new CampaignStatisticDatagridListener($this->segmentHelper);
    }

    /**
     * @param string $gridName
     * @param array  $parameters
     * @param array  $select
     * @param string $groupBy
     * @param string $expected
     *
     * @dataProvider preBuildDataProvider
     */
    public function testOnPreBuild($gridName, array $parameters, array $select, $groupBy, $expected)
    {
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

        $this->segmentHelper
            ->expects($this->any())
            ->method('getSegmentIdByGridName')
            ->with($this->equalTo($gridName))
            ->will($this->returnValue(true));

        $this->segmentHelper
            ->expects($this->any())
            ->method('getMarketingListBySegment')
            ->with($this->equalTo(true))
            ->will($this->returnValue(new \stdClass()));

        $this->listener->onPreBuild($event);

        $this->assertEquals($expected, $config->offsetGetByPath(CampaignStatisticDatagridListener::PATH_GROUPBY));
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function preBuildDataProvider()
    {
        return [
            [
                'gridName'   => 'gridName',
                'parameters' => [],
                'selects'    => [],
                'groupBy'    => null,
                'expected'   => null,
            ],
            [
                'gridName'   => Segment::GRID_PREFIX,
                'parameters' => [],
                'selects'    => [],
                'groupBy'    => null,
                'expected'   => null,
            ],
            [
                'gridName'   => Segment::GRID_PREFIX,
                'parameters' => [MarketingListItemsListener::MIXIN => 'wrong'],
                'selects'    => [],
                'groupBy'    => null,
                'expected'   => null,
            ],
            [
                'gridName'   => Segment::GRID_PREFIX,
                'parameters' => [MarketingListItemsListener::MIXIN => CampaignStatisticDatagridListener::MIXIN_NAME],
                'selects'    => [],
                'groupBy'    => null,
                'expected'   => null,
            ],
            [
                'gridName'   => Segment::GRID_PREFIX,
                'parameters' => [MarketingListItemsListener::MIXIN => CampaignStatisticDatagridListener::MIXIN_NAME],
                'selects'    => [],
                'groupBy'    => 'alias.existing',
                'expected'   => 'alias.existing',
            ],
            [
                'gridName'   => Segment::GRID_PREFIX,
                'parameters' => [MarketingListItemsListener::MIXIN => CampaignStatisticDatagridListener::MIXIN_NAME],
                'selects'    => ['alias.field'],
                'groupBy'    => null,
                'expected'   => null,
            ],
            [
                'gridName'   => Segment::GRID_PREFIX,
                'parameters' => [MarketingListItemsListener::MIXIN => CampaignStatisticDatagridListener::MIXIN_NAME],
                'selects'    => ['alias.field', 'alias.matchedFields as c1', 'alias.secondMatched as c2'],
                'groupBy'    => null,
                'expected'   => 'alias.matchedFields, alias.secondMatched',
            ],
            [
                'gridName'   => Segment::GRID_PREFIX,
                'parameters' => [MarketingListItemsListener::MIXIN => CampaignStatisticDatagridListener::MIXIN_NAME],
                'selects'    => ['alias.field', 'alias.matchedFields as c1'],
                'groupBy'    => 'alias.existing',
                'expected'   => 'alias.matchedFields, alias.existing',
            ],
            [
                'gridName'   => Segment::GRID_PREFIX,
                'parameters' => [MarketingListItemsListener::MIXIN => CampaignStatisticDatagridListener::MIXIN_NAME],
                'selects'    => ['alias.matchedFields as c1wrong'],
                'groupBy'    => null,
                'expected'   => null,
            ],
            [
                'gridName'   => Segment::GRID_PREFIX,
                'parameters' => [MarketingListItemsListener::MIXIN => CampaignStatisticDatagridListener::MIXIN_NAME],
                'selects'    => ['alias.matchedFields as wrongc1'],
                'groupBy'    => null,
                'expected'   => null,
            ],
            [
                'gridName'   => Segment::GRID_PREFIX,
                'parameters' => [MarketingListItemsListener::MIXIN => CampaignStatisticDatagridListener::MIXIN_NAME],
                'selects'    => ['alias.matchedFields wrongas c1'],
                'groupBy'    => null,
                'expected'   => null,
            ],
            [
                'gridName'   => Segment::GRID_PREFIX,
                'parameters' => [MarketingListItemsListener::MIXIN => CampaignStatisticDatagridListener::MIXIN_NAME],
                'selects'    => ['alias.matchedFields aswrong c1'],
                'groupBy'    => null,
                'expected'   => null,
            ],
            [
                'gridName'   => Segment::GRID_PREFIX,
                'parameters' => [MarketingListItemsListener::MIXIN => CampaignStatisticDatagridListener::MIXIN_NAME],
                'selects'    => ['alias.matchedFields as ca'],
                'groupBy'    => null,
                'expected'   => null,
            ],
            [
                'gridName'   => Segment::GRID_PREFIX,
                'parameters' => [MarketingListItemsListener::MIXIN => CampaignStatisticDatagridListener::MIXIN_NAME],
                'selects'    => ['alias.matchedFields as c100'],
                'groupBy'    => null,
                'expected'   => 'alias.matchedFields',
            ],
            [
                'gridName'   => Segment::GRID_PREFIX,
                'parameters' => [MarketingListItemsListener::MIXIN => CampaignStatisticDatagridListener::MIXIN_NAME],
                'selects'    => ['alias.matchedFields   as   c1'],
                'groupBy'    => null,
                'expected'   => 'alias.matchedFields',
            ]
        ];
    }

    /**
     * @param string                                   $gridName
     * @param array                                    $parameters
     * @param \PHPUnit_Framework_MockObject_MockObject $dataSource
     * @param bool                                     $expected
     *
     * @dataProvider onBuildAfterDataSource
     */
    public function testOnBuildAfter($gridName, $parameters, $dataSource, $expected)
    {
        $grid = $this->getMock('Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface');

        $grid
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($gridName));

        $grid
            ->expects($this->any())
            ->method('getDatasource')
            ->will($this->returnValue($dataSource));

        $grid
            ->expects($this->once())
            ->method('getParameters')
            ->will($this->returnValue(new ParameterBag($parameters)));

        $this->segmentHelper
            ->expects($this->any())
            ->method('getSegmentIdByGridName')
            ->with($this->equalTo($gridName))
            ->will($this->returnValue(true));

        $this->segmentHelper
            ->expects($this->any())
            ->method('getMarketingListBySegment')
            ->with($this->equalTo(true))
            ->will($this->returnValue(new \stdClass()));

        if ($expected) {
            $qb = $this
                ->getMockBuilder('Doctrine\ORM\QueryBuilder')
                ->disableOriginalConstructor()
                ->getMock();

            $dataSource
                ->expects($this->once())
                ->method('getQueryBuilder')
                ->will($this->returnValue($qb));
        }

        $event = new BuildAfter($grid);
        $this->listener->onBuildAfter($event);
    }

    /**
     * @return array
     */
    public function onBuildAfterDataSource()
    {
        $ormDataSource = $this
            ->getMockBuilder('Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource')
            ->disableOriginalConstructor()
            ->getMock();

        $dataSource = $this
            ->getMockBuilder('Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface')
            ->disableOriginalConstructor()
            ->getMock();

        return [
            [
                'gridName'   => Segment::GRID_PREFIX,
                'parameters' => [],
                'dataSource' => $dataSource,
                'expected'   => false,
            ],
            [
                'gridName'   => Segment::GRID_PREFIX,
                'parameters' => [
                    MarketingListItemsListener::MIXIN => CampaignStatisticDatagridListener::MIXIN_NAME,
                    'emailCampaign'                   => 1
                ],
                'dataSource' => $dataSource,
                'expected'   => false,
            ],
            [
                'gridName'   => Segment::GRID_PREFIX,
                'parameters' => [
                    MarketingListItemsListener::MIXIN => CampaignStatisticDatagridListener::MIXIN_NAME,
                    'emailCampaign'                   => 1
                ],
                'dataSource' => $ormDataSource,
                'expected'   => true,
            ]
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Parameter "emailCampaign" is missing
     */
    public function testEmailCampaignParameterMissing()
    {
        $grid = $this->getMock('Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface');

        $grid
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue(Segment::GRID_PREFIX));

        $grid
            ->expects($this->once())
            ->method('getParameters')
            ->will(
                $this->returnValue(
                    new ParameterBag(
                        [MarketingListItemsListener::MIXIN => CampaignStatisticDatagridListener::MIXIN_NAME]
                    )
                )
            );

        $this->segmentHelper
            ->expects($this->any())
            ->method('getSegmentIdByGridName')
            ->with($this->equalTo(Segment::GRID_PREFIX))
            ->will($this->returnValue(true));

        $this->segmentHelper
            ->expects($this->any())
            ->method('getMarketingListBySegment')
            ->with($this->equalTo(true))
            ->will($this->returnValue(new \stdClass()));

        $event = new BuildAfter($grid);
        $this->listener->onBuildAfter($event);
    }
}
