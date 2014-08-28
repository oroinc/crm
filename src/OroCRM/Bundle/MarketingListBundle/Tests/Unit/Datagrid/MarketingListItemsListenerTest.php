<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Datagrid;

use Oro\Bundle\SegmentBundle\Entity\Segment;
use OroCRM\Bundle\MarketingListBundle\Datagrid\MarketingListItemsListener;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Model\DataGridConfigurationHelper;
use OroCRM\Bundle\MarketingListBundle\Model\MarketingListSegmentHelper;

class MarketingListItemsListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MarketingListItemsListener
     */
    protected $listener;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DataGridConfigurationHelper
     */
    protected $dataGridHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|MarketingListSegmentHelper
     */
    protected $segmentHelper;

    protected function setUp()
    {
        $this->dataGridHelper = $this
            ->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Model\DataGridConfigurationHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->segmentHelper = $this
            ->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Model\MarketingListSegmentHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new MarketingListItemsListener($this->dataGridHelper, $this->segmentHelper);
    }

    /**
     * @param string $gridName
     * @param bool   $isApplicable
     *
     * @dataProvider preBuildDataProvider
     */
    public function testOnPreBuild($gridName, $isApplicable)
    {
        $event = $this
            ->getMockBuilder('Oro\Bundle\DataGridBundle\Event\PreBuild')
            ->disableOriginalConstructor()
            ->getMock();

        $config = $this
            ->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration')
            ->disableOriginalConstructor()
            ->getMock();

        $event
            ->expects($this->exactly(1 + (int)$isApplicable))
            ->method('getConfig')
            ->will($this->returnValue($config));

        $config
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($gridName));

        $this->segmentHelper
            ->expects($this->once())
            ->method('getSegmentIdByGridName')
            ->with($this->equalTo($gridName))
            ->will($this->returnValue((int)$isApplicable));

        $this->segmentHelper
            ->expects($this->any())
            ->method('getMarketingListBySegment')
            ->with($this->equalTo((int)$isApplicable))
            ->will($this->returnValue(new MarketingList()));

        if ($isApplicable) {
            $this->dataGridHelper
                ->expects($this->once())
                ->method('extendConfiguration')
                ->with($this->equalTo($config), $this->equalTo(MarketingListItemsListener::MIXIN_NAME));
        } else {
            $this->dataGridHelper
                ->expects($this->never())
                ->method('extendConfiguration');
        }

        $this->listener->onPreBuild($event);
    }

    /**
     * @return array
     */
    public function preBuildDataProvider()
    {
        return [
            ['gridName', false],
            [Segment::GRID_PREFIX, false],
            [Segment::GRID_PREFIX . '1', true],
        ];
    }

    /**
     * @param string $gridName
     * @param bool   $useDataSource
     *
     * @dataProvider buildAfterDataProvider
     */
    public function testOnBuildAfter($gridName, $useDataSource)
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

        /** @var MarketingList $marketingList */
        $this->segmentHelper
            ->expects($this->exactly(1 + (int)$useDataSource))
            ->method('getSegmentIdByGridName')
            ->with($this->equalTo($gridName))
            ->will($this->returnValue($marketingList->getId()));

        $this->segmentHelper
            ->expects($this->exactly(1 + (int)$useDataSource))
            ->method('getMarketingListBySegment')
            ->with($this->equalTo($marketingList->getId()))
            ->will($this->returnValue($marketingList));

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

        $dataSource
            ->expects($this->exactly((int)$useDataSource))
            ->method('getQueryBuilder')
            ->will($this->returnValue($qb));

        $datagrid
            ->expects($this->once())
            ->method('getDatasource')
            ->will($this->returnValue($useDataSource ? $dataSource : null));

        $this->listener->onBuildAfter($event);
    }

    /**
     * @return array
     */
    public function buildAfterDataProvider()
    {
        return [
            ['gridName', false],
            ['gridName', true],
            [Segment::GRID_PREFIX, false],
            [Segment::GRID_PREFIX, true],
            [Segment::GRID_PREFIX . '1', false],
            [Segment::GRID_PREFIX . '1', true],
        ];
    }
}
