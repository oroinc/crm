<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use OroCRM\Bundle\CampaignBundle\EventListener\CampaignStatisticDatagridListener;
use OroCRM\Bundle\MarketingListBundle\Datagrid\MarketingListItemsListener;
use OroCRM\Bundle\MarketingListBundle\Datagrid\ConfigurationProvider;

class CampaignStatisticDatagridListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CampaignStatisticDatagridListener
     */
    protected $listener;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $marketingListHelper;

    protected function setUp()
    {
        $this->marketingListHelper = $this
            ->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Model\MarketingListHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new CampaignStatisticDatagridListener($this->marketingListHelper);
    }

    /**
     * @dataProvider applicableDataProvider
     * @param string|null $mixin
     * @param string $gridName
     * @param bool $isCorrectMixin
     * @param int|null $id
     * @param bool $expected
     */
    public function testIsApplicable($mixin, $gridName, $isCorrectMixin, $id, $expected)
    {
        $parametersBag = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $parametersBag->expects($this->once())
            ->method('get')
            ->with(MarketingListItemsListener::MIXIN, false)
            ->will($this->returnValue($mixin));

        if ($isCorrectMixin) {
            $this->marketingListHelper->expects($this->once())
                ->method('getMarketingListIdByGridName')
                ->with($gridName)
                ->will($this->returnValue($id));
        }

        $this->assertEquals($expected, $this->listener->isApplicable($gridName, $parametersBag));
    }

    public function applicableDataProvider()
    {
        return [
            [null, 'test_grid', false, null, false],
            ['some_mixin', 'test_grid', false, null, false],
            [CampaignStatisticDatagridListener::MIXIN_NAME, 'test_grid', true, null, false],
            [CampaignStatisticDatagridListener::MANUAL_MIXIN_NAME, 'test_grid', true, null, false],
            [CampaignStatisticDatagridListener::MIXIN_NAME, 'test_grid', true, 1, true],
            [CampaignStatisticDatagridListener::MANUAL_MIXIN_NAME, 'test_grid', true, 1, true],
        ];
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

        $this->marketingListHelper
            ->expects($this->any())
            ->method('getMarketingListIdByGridName')
            ->with($this->equalTo($gridName))
            ->will($this->returnValue(1));

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
            'no mixin' => [
                'gridName'   => ConfigurationProvider::GRID_PREFIX,
                'parameters' => [],
                'selects'    => [],
                'groupBy'    => null,
                'expected'   => null,
            ],
            'wrong mixin' => [
                'gridName'   => ConfigurationProvider::GRID_PREFIX,
                'parameters' => [MarketingListItemsListener::MIXIN => 'wrong'],
                'selects'    => [],
                'groupBy'    => null,
                'expected'   => null,
            ],
            'no fields' => [
                'gridName'   => ConfigurationProvider::GRID_PREFIX,
                'parameters' => [MarketingListItemsListener::MIXIN => CampaignStatisticDatagridListener::MIXIN_NAME],
                'selects'    => [],
                'groupBy'    => null,
                'expected'   => null,
            ],
            'group by no fields' => [
                'gridName'   => ConfigurationProvider::GRID_PREFIX,
                'parameters' => [
                    MarketingListItemsListener::MIXIN => CampaignStatisticDatagridListener::MANUAL_MIXIN_NAME
                ],
                'selects'    => [],
                'groupBy'    => 'alias.existing',
                'expected'   => 'alias.existing',
            ],
            'field without alias' => [
                'gridName'   => ConfigurationProvider::GRID_PREFIX,
                'parameters' => [MarketingListItemsListener::MIXIN => CampaignStatisticDatagridListener::MIXIN_NAME],
                'selects'    => ['alias.field'],
                'groupBy'    => null,
                'expected'   => 'alias.field',
            ],
            'aliases and without' => [
                'gridName'   => ConfigurationProvider::GRID_PREFIX,
                'parameters' => [
                    MarketingListItemsListener::MIXIN => CampaignStatisticDatagridListener::MANUAL_MIXIN_NAME
                ],
                'selects'    => ['alias.field', 'alias.matchedFields  as  c1', 'alias.secondMatched aS someAlias3'],
                'groupBy'    => null,
                'expected'   => 'alias.field, c1, someAlias3',
            ],
            'mixed fields and group by' => [
                'gridName'   => ConfigurationProvider::GRID_PREFIX,
                'parameters' => [MarketingListItemsListener::MIXIN => CampaignStatisticDatagridListener::MIXIN_NAME],
                'selects'    => ['alias.field', 'alias.matchedFields as c1'],
                'groupBy'    => 'alias.existing',
                'expected'   => 'alias.field, c1, alias.existing',
            ],
            'wrong field definition' => [
                'gridName'   => ConfigurationProvider::GRID_PREFIX,
                'parameters' => [
                    MarketingListItemsListener::MIXIN => CampaignStatisticDatagridListener::MANUAL_MIXIN_NAME
                ],
                'selects'    => ['alias.matchedFields wrongas c1'],
                'groupBy'    => null,
                'expected'   => null,
            ],
            'with aggregate' => [
                'gridName'   => ConfigurationProvider::GRID_PREFIX,
                'parameters' => [MarketingListItemsListener::MIXIN => CampaignStatisticDatagridListener::MIXIN_NAME],
                'selects'    => ['MAX(t1.f0)', 'AvG(t10.F19) as agF1', 'alias.matchedFields AS c1'],
                'groupBy'    => null,
                'expected'   => 'c1',
            ],
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

        $this->marketingListHelper
            ->expects($this->any())
            ->method('getSegmentIdByGridName')
            ->with($this->equalTo($gridName))
            ->will($this->returnValue(true));

        $this->marketingListHelper
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
                'gridName'   => ConfigurationProvider::GRID_PREFIX,
                'parameters' => [],
                'dataSource' => $dataSource,
                'expected'   => false,
            ],
            [
                'gridName'   => ConfigurationProvider::GRID_PREFIX,
                'parameters' => [
                    MarketingListItemsListener::MIXIN => CampaignStatisticDatagridListener::MIXIN_NAME,
                    'emailCampaign'                   => 1
                ],
                'dataSource' => $dataSource,
                'expected'   => false,
            ],
            [
                'gridName'   => ConfigurationProvider::GRID_PREFIX,
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
            ->will($this->returnValue(ConfigurationProvider::GRID_PREFIX));

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

        $this->marketingListHelper
            ->expects($this->once())
            ->method('getMarketingListIdByGridName')
            ->with($this->equalTo(ConfigurationProvider::GRID_PREFIX))
            ->will($this->returnValue(1));

        $event = new BuildAfter($grid);
        $this->listener->onBuildAfter($event);
    }
}
