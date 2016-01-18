<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Provider;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Oro\Bundle\TagBundle\Grid\TagsExtension;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListType;
use OroCRM\Bundle\MarketingListBundle\Datagrid\ConfigurationProvider;
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

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $em;

    protected function setUp()
    {
        $this->dataGridManager = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new MarketingListProvider($this->dataGridManager);

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown()
    {
        unset($this->provider, $this->dataGridManager, $this->em);
    }

    /**
     * Gets mock object for query builder
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getQueryBuilder()
    {
        return $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->setConstructorArgs([$this->em])
            ->getMock();
    }

    /**
     * @dataProvider queryBuilderDataProvider
     * @param string $type
     */
    public function testGetMarketingListQueryBuilder($type)
    {
        $marketingList = $this->getMarketingList($type);
        $queryBuilder = $this->getQueryBuilder();
        $dataGrid = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface')
            ->getMockForAbstractClass();
        $this->assertGetQueryBuilder($marketingList, $queryBuilder, $dataGrid);

        $this->assertEquals($queryBuilder, $this->provider->getMarketingListQueryBuilder($marketingList));
    }

    public function queryBuilderDataProvider()
    {
        return [
            [MarketingListType::TYPE_MANUAL],
            [MarketingListType::TYPE_DYNAMIC],
            [MarketingListType::TYPE_STATIC],
        ];
    }

    /**
     * @dataProvider queryBuilderDataProvider
     * @param string $type
     */
    public function testGetMarketingListResultIterator($type)
    {
        if ($type === MarketingListType::TYPE_MANUAL) {
            $mixin = MarketingListProvider::MANUAL_RESULT_ITEMS_MIXIN;
        } else {
            $mixin = MarketingListProvider::RESULT_ITEMS_MIXIN;
        }

        $marketingList = $this->getMarketingList($type);
        $queryBuilder = $this->getQueryBuilder();
        $dataGrid = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface')
            ->getMockForAbstractClass();
        $config = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration')
            ->disableOriginalConstructor()
            ->getMock();
        $config->expects($this->once())
            ->method('offsetGetByPath')
            ->with(DatagridConfiguration::DATASOURCE_SKIP_COUNT_WALKER_PATH)
            ->will($this->returnValue(true));
        $dataGrid->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($config));

        $this->assertGetQueryBuilder(
            $marketingList,
            $queryBuilder,
            $dataGrid,
            $mixin
        );

        $this->assertInstanceOf('\Iterator', $this->provider->getMarketingListResultIterator($marketingList));
    }

    /**
     * @dataProvider queryBuilderDataProvider
     * @param string $type
     */
    public function testGetMarketingListEntitiesQueryBuilder($type)
    {
        $marketingList = $this->getMarketingList($type);

        $from = $this->getMockBuilder('Doctrine\ORM\Query\Expr\From')
            ->disableOriginalConstructor()
            ->getMock();
        $from->expects($this->once())
            ->method('getAlias')
            ->will($this->returnValue('alias'));
        $queryBuilder = $this->assertEntitiesQueryBuilder($marketingList, 'alias');
        $queryBuilder->expects($this->once())
            ->method('getDQLPart')
            ->with('from')
            ->will($this->returnValue([$from]));

        $this->assertInstanceOf(
            'Doctrine\ORM\QueryBuilder',
            $this->provider->getMarketingListEntitiesQueryBuilder($marketingList)
        );
    }

    /**
     * @dataProvider queryBuilderDataProvider
     * @param string $type
     */
    public function testGetMarketingListEntitiesIterator($type)
    {
        $marketingList = $this->getMarketingList($type);

        $from = $this->getMockBuilder('Doctrine\ORM\Query\Expr\From')
            ->disableOriginalConstructor()
            ->getMock();
        $from->expects($this->once())
            ->method('getAlias')
            ->will($this->returnValue('alias'));
        $queryBuilder = $this->assertEntitiesQueryBuilder($marketingList, 'alias');
        $queryBuilder->expects($this->once())
            ->method('getDQLPart')
            ->with('from')
            ->will($this->returnValue([$from]));

        $this->assertInstanceOf('\Iterator', $this->provider->getMarketingListEntitiesIterator($marketingList));
    }

    /**
     * @param MarketingList $marketingList
     * @param string $alias
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function assertEntitiesQueryBuilder(MarketingList $marketingList, $alias)
    {
        if ($marketingList->isManual()) {
            $mixin = MarketingListProvider::MANUAL_RESULT_ENTITIES_MIXIN;
        } else {
            $mixin = MarketingListProvider::RESULT_ENTITIES_MIXIN;
        }

        $queryBuilder = $this->getQueryBuilder();
        $dataGrid = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface')
            ->getMockForAbstractClass();

        $queryBuilder->expects($this->exactly(2))
            ->method('resetDQLPart')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())
            ->method('select')
            ->with($alias)
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())
            ->method('orderBy')
            ->with($alias . '.id')
            ->will($this->returnSelf());

        $this->assertGetQueryBuilder(
            $marketingList,
            $queryBuilder,
            $dataGrid,
            $mixin
        );

        return $queryBuilder;
    }

    protected function assertGetQueryBuilder(MarketingList $marketingList, $queryBuilder, $dataGrid, $mixin = null)
    {
        $dataSource = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource')
            ->disableOriginalConstructor()
            ->getMock();
        $dataSource->expects($this->once())
            ->method('getQueryBuilder')
            ->will($this->returnValue($queryBuilder));
        $dataGrid->expects($this->once())
            ->method('getAcceptedDatasource')
            ->will($this->returnValue($dataSource));

        $parameters = [
            PagerInterface::PAGER_ROOT_PARAM => [PagerInterface::DISABLED_PARAM => true],
            TagsExtension::TAGS_ROOT_PARAM => [PagerInterface::DISABLED_PARAM => true],
        ];
        if ($mixin) {
            $parameters['grid-mixin'] = $mixin;
        }
        $this->dataGridManager->expects($this->atLeastOnce())
            ->method('getDatagrid')
            ->with(ConfigurationProvider::GRID_PREFIX . $marketingList->getId(), $parameters)
            ->will($this->returnValue($dataGrid));
    }

    protected function getMarketingList($typeName)
    {
        $type = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingListType')
            ->disableOriginalConstructor()
            ->getMock();
        $type->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($typeName));

        $marketingList = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();
        $marketingList->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($type));
        $marketingList->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $marketingList->expects($this->any())
            ->method('isManual')
            ->will($this->returnValue($typeName === MarketingListType::TYPE_MANUAL));

        return $marketingList;
    }
}
