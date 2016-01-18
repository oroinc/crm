<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Provider;

use Doctrine\ORM\Query\Expr\Select;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Oro\Bundle\TagBundle\Grid\TagsExtension;
use Oro\Bundle\DataGridBundle\Datagrid\Manager;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListType;
use OroCRM\Bundle\MarketingListBundle\Datagrid\ConfigurationProvider;
use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListProvider;

class MarketingListProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Manager
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
        unset($this->provider, $this->dataGridManager);
    }

    /**
     * Gets mock object for query builder
     *
     * @param array $dqlParts
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getQueryBuilder(array $dqlParts = [])
    {
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();

        $select = new Select();
        $select->add('t0.test as c1');

        $dqlParts[] = ['select', [$select]];

        $qb->expects($this->any())
            ->method('getDQLPart')
            ->will($this->returnValueMap($dqlParts));

        return $qb;
    }

    /**
     * @dataProvider queryBuilderDataProvider
     * @param string $type
     */
    public function testGetMarketingListQueryBuilder($type)
    {
        $marketingList = $this->getMarketingList($type);
        $queryBuilder = $this->getQueryBuilder();
        $dataGrid = $this->getDataGrid();
        $this->assertGetQueryBuilder($marketingList, $queryBuilder, $dataGrid);

        $this->assertEquals($queryBuilder, $this->provider->getMarketingListQueryBuilder($marketingList));
        $expectedColumnInformation = ['testField' => 't0.test'];
        $this->assertEquals($expectedColumnInformation, $this->provider->getColumnInformation($marketingList));
    }

    /**
     * @return array
     */
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
        $dataGrid = $this->getDataGrid();
        $config = $dataGrid->getConfig();
        $config->offsetSetByPath(DatagridConfiguration::DATASOURCE_SKIP_COUNT_WALKER_PATH, true);

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
        $queryBuilder = $this->getQueryBuilder([['from', [$from]]]);
        $this->assertEntitiesQueryBuilder($queryBuilder, $marketingList, 'alias');

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
        $queryBuilder = $this->getQueryBuilder([['from', [$from]]]);
        $this->assertEntitiesQueryBuilder($queryBuilder, $marketingList, 'alias');

        $this->assertInstanceOf('\Iterator', $this->provider->getMarketingListEntitiesIterator($marketingList));
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $queryBuilder
     * @param MarketingList $marketingList
     * @param string $alias
     */
    protected function assertEntitiesQueryBuilder($queryBuilder, MarketingList $marketingList, $alias)
    {
        if ($marketingList->isManual()) {
            $mixin = MarketingListProvider::MANUAL_RESULT_ENTITIES_MIXIN;
        } else {
            $mixin = MarketingListProvider::RESULT_ENTITIES_MIXIN;
        }

        $dataGrid = $this->getDataGrid();

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
    }

    /**
     * @param MarketingList $marketingList
     * @param \PHPUnit_Framework_MockObject_MockObject $queryBuilder
     * @param \PHPUnit_Framework_MockObject_MockObject $dataGrid
     * @param null|string $mixin
     */
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

    /**
     * @param string $typeName
     * @return \PHPUnit_Framework_MockObject_MockObject|MarketingList
     */
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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DatagridInterface
     */
    protected function getDataGrid()
    {
        $dataGrid = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface')
            ->getMockForAbstractClass();

        $columnAliases = ['testField' => 'c1'];
        $config = DatagridConfiguration::createNamed('test', []);
        $config->offsetSetByPath(MarketingListProvider::DATAGRID_COLUMN_ALIASES_PATH, $columnAliases);

        $dataGrid->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($config));

        return $dataGrid;
    }
}
