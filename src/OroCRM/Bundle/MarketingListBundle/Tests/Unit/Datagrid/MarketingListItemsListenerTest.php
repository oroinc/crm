<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\EventListener\MixinListener;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use OroCRM\Bundle\MarketingListBundle\Datagrid\MarketingListItemsListener;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

class MarketingListItemsListenerTest extends \PHPUnit_Framework_TestCase
{
    const MIXIN_NAME = 'new-mixin-for-test-grid';

    /**
     * @var MarketingListItemsListener
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

        $this->listener = new MarketingListItemsListener($this->marketingListHelper);
    }

    /**
     * @param string $gridName
     * @param bool   $useDataSource
     * @param bool   $hasParameter
     *
     * @dataProvider buildAfterDataProvider
     */
    public function testOnBuildAfter($gridName, $useDataSource, $hasParameter)
    {
        $marketingList = $this
            ->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();

        $marketingList
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        $event = $this
            ->getMockBuilder('Oro\Bundle\DataGridBundle\Event\BuildAfter')
            ->disableOriginalConstructor()
            ->getMock();

        $datagrid = $this->getMock('Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface');

        $event
            ->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($datagrid));

        $datagrid
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($gridName));

        $parameters = [];
        if ($hasParameter) {
            $parameters = [MixinListener::GRID_MIXIN => self::MIXIN_NAME];
        }

        $datagrid
            ->expects($this->once())
            ->method('getParameters')
            ->will($this->returnValue(new ParameterBag($parameters)));

        /** @var MarketingList $marketingList */
        if ($hasParameter) {
            $this->marketingListHelper
                ->expects($this->exactly(1 + (int)$useDataSource))
                ->method('getMarketingListIdByGridName')
                ->with($this->equalTo($gridName))
                ->will($this->returnValue($marketingList->getId()));

            if ((int)$useDataSource) {
                $this->marketingListHelper
                    ->expects($this->exactly((int)$useDataSource))
                    ->method('getMarketingList')
                    ->with($this->equalTo($marketingList->getId()))
                    ->will($this->returnValue($marketingList));
            } else {
                $this->marketingListHelper
                    ->expects($this->never())
                    ->method('getMarketingList');
            }
        }

        $qb = $this
            ->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $qb
            ->expects($this->any())
            ->method('addSelect')
            ->will($this->returnSelf());

        $qb
            ->expects($this->any())
            ->method('setParameter')
            ->will($this->returnSelf());

        $dataSource = $this
            ->getMockBuilder('Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource')
            ->disableOriginalConstructor()
            ->getMock();

        if ($hasParameter) {
            $dataSource
                ->expects($this->exactly((int)$useDataSource))
                ->method('getQueryBuilder')
                ->will($this->returnValue($qb));

            $datagrid
                ->expects($this->once())
                ->method('getDatasource')
                ->will($this->returnValue($useDataSource ? $dataSource : null));
        }

        $this->listener->onBuildAfter($event);
    }

    /**
     * @return array
     */
    public function buildAfterDataProvider()
    {
        return [
            ['gridName', false, false],
            ['gridName', false, true],
            ['gridName', true, false],
            ['gridName', true, true],
            [Segment::GRID_PREFIX, false, false],
            [Segment::GRID_PREFIX, false, true],
            [Segment::GRID_PREFIX, true, false],
            [Segment::GRID_PREFIX, true, true],
            [Segment::GRID_PREFIX . '1', false, false],
            [Segment::GRID_PREFIX . '1', false, true],
            [Segment::GRID_PREFIX . '1', true, false],
            [Segment::GRID_PREFIX . '1', true, true],
        ];
    }

    /**
     * @param string $gridName
     * @param bool   $hasParameter
     * @param bool   $isApplicable
     * @param bool   $expected
     *
     * @dataProvider onBuildBeforeDataProvider
     */
    public function testOnBuildBefore($gridName, $hasParameter, $isApplicable, $expected)
    {
        $event = $this
            ->getMockBuilder('Oro\Bundle\DataGridBundle\Event\BuildBefore')
            ->disableOriginalConstructor()
            ->getMock();

        $datagrid = $this->getMock('Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface');

        $event
            ->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($datagrid));

        $parameters = [];
        if ($hasParameter) {
            $parameters = [MixinListener::GRID_MIXIN => self::MIXIN_NAME];

            $this->marketingListHelper
                ->expects($this->once())
                ->method('getMarketingListIdByGridName')
                ->with($this->equalTo($gridName))
                ->will($this->returnValue((int)$isApplicable));
        }

        $datagrid
            ->expects($this->once())
            ->method('getParameters')
            ->will($this->returnValue(new ParameterBag($parameters)));

        $datagrid
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($gridName));

        if ($expected) {
            $event
                ->expects($this->once())
                ->method('stopPropagation');
        }

        $this->listener->onBuildBefore($event);
    }

    /**
     * @return array
     */
    public function onBuildBeforeDataProvider()
    {
        return [
            ['gridName', false, false, false],
            ['gridName', false, true, false],
            ['gridName', true, false, false],
            ['gridName', true, true, true],
        ];
    }
}
