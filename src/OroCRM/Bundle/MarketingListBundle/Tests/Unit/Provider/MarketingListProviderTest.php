<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Provider;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListType;
use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListProvider;

class MarketingListProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataGridManager;

    /**
     * @var MarketingListProvider
     */
    protected $provider;

    protected function setUp()
    {
        $this->dataGridManager = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new MarketingListProvider($this->dataGridManager);
    }

    protected function tearDown()
    {
        unset($this->provider);
        unset($this->dataGridManager);
    }

    public function testGetMarketingListQueryBuilderManual()
    {
        $marketingList = $this->getMarketingList(MarketingListType::TYPE_MANUAL);
        $this->assertNull($this->provider->getMarketingListQueryBuilder($marketingList));
    }

    public function testGetMarketingListQueryBuilderBySegment()
    {
        $marketingList = $this->getMarketingList(MarketingListType::TYPE_DYNAMIC);
        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $dataGrid = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface')
            ->getMockForAbstractClass();
        $this->assertGetQueryBuilder($marketingList, $queryBuilder, $dataGrid);

        $this->assertEquals($queryBuilder, $this->provider->getMarketingListQueryBuilder($marketingList));
    }

    public function testGetMarketingListResultIteratorManual()
    {
        $marketingList = $this->getMarketingList(MarketingListType::TYPE_MANUAL);
        $this->assertNull($this->provider->getMarketingListResultIterator($marketingList));
    }

    public function testGetMarketingListResultIterator()
    {
        $marketingList = $this->getMarketingList(MarketingListType::TYPE_DYNAMIC);
        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $dataGrid = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface')
            ->getMockForAbstractClass();
        $config = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration')
            ->disableOriginalConstructor()
            ->getMock();
        $config->expects($this->once())
            ->method('offsetGetByPath')
            ->with(Builder::DATASOURCE_SKIP_COUNT_WALKER_PATH)
            ->will($this->returnValue(true));
        $dataGrid->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($config));

        $this->assertGetQueryBuilder($marketingList, $queryBuilder, $dataGrid);

        $this->assertInstanceOf('\Iterator', $this->provider->getMarketingListResultIterator($marketingList));
    }

    protected function assertGetQueryBuilder($marketingList, $queryBuilder, $dataGrid)
    {
        $segment = $this->getMockBuilder('Oro\Bundle\SegmentBundle\Entity\Segment')
            ->disableOriginalConstructor()
            ->getMock();
        $segment->expects($this->atLeastOnce())
            ->method('getGridPrefix')
            ->will($this->returnValue('grid_prefix_'));
        $segment->expects($this->atLeastOnce())
            ->method('getId')
            ->will($this->returnValue(1));
        $marketingList->expects($this->atLeastOnce())
            ->method('getSegment')
            ->will($this->returnValue($segment));

        $dataSource = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource')
            ->disableOriginalConstructor()
            ->getMock();
        $dataSource->expects($this->once())
            ->method('getQueryBuilder')
            ->will($this->returnValue($queryBuilder));
        $dataGrid->expects($this->once())
            ->method('getDatasource')
            ->will($this->returnValue($dataSource));
        $this->dataGridManager->expects($this->atLeastOnce())
            ->method('getDatagrid')
            ->with(
                'grid_prefix_1',
                array(PagerInterface::PAGER_ROOT_PARAM => array(PagerInterface::DISABLED_PARAM => true))
            )
            ->will($this->returnValue($dataGrid));
    }

    protected function getMarketingList($typeName)
    {
        $type = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingListType')
            ->disableOriginalConstructor()
            ->getMock();
        $type->expects($this->atLeastOnce())
            ->method('getName')
            ->will($this->returnValue($typeName));

        $marketingList = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();
        $marketingList->expects($this->atLeastOnce())
            ->method('getType')
            ->will($this->returnValue($type));

        return $marketingList;
    }
}
