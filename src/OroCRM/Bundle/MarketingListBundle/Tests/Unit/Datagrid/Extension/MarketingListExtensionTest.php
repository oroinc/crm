<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Datagrid\Extension;

use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Func;
use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use OroCRM\Bundle\MarketingListBundle\Datagrid\ConfigurationProvider;
use OroCRM\Bundle\MarketingListBundle\Datagrid\Extension\MarketingListExtension;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

class MarketingListExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MarketingListExtension
     */
    protected $extension;

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

        $this->extension = new MarketingListExtension($this->marketingListHelper);
    }

    public function testIsApplicableIncorrectDataSource()
    {
        $config = $this
            ->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration')
            ->disableOriginalConstructor()
            ->getMock();
        $config
            ->expects($this->exactly(2))
            ->method('offsetGetByPath')
            ->will(
                $this->returnValueMap(
                    [
                        [Builder::DATASOURCE_TYPE_PATH, null, 'INCORRECT'],
                        ['[name]', null, 'grid'],
                    ]
                )
            );

        $this->assertFalse($this->extension->isApplicable($config));
    }

    public function testIsApplicableVisitTwice()
    {
        $config = $this
            ->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration')
            ->disableOriginalConstructor()
            ->getMock();

        $config
            ->expects($this->any())
            ->method('offsetGetByPath')
            ->will(
                $this->returnValueMap(
                    [
                        ['[name]', null, ConfigurationProvider::GRID_PREFIX . '1'],
                        [Builder::DATASOURCE_TYPE_PATH, null, OrmDatasource::TYPE],
                        [MarketingListExtension::OPTIONS_MIXIN_PATH, false, true]
                    ]
                )
            );

        $this->marketingListHelper->expects($this->any())
            ->method('getMarketingListIdByGridName')
            ->with(ConfigurationProvider::GRID_PREFIX . '1')
            ->will($this->returnValue(1));

        $this->marketingListHelper->expects($this->any())
            ->method('getMarketingList')
            ->with(1)
            ->will($this->returnValue(new MarketingList()));

        $this->assertTrue($this->extension->isApplicable($config));

        $qb = $this->getQbMock();
        $dataSource = $this
            ->getMockBuilder('Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource')
            ->disableOriginalConstructor()
            ->getMock();

        $condition = new Andx();
        $condition->add('argument');

        $qb
            ->expects($this->once())
            ->method('getDQLParts')
            ->will($this->returnValue(['where' => $condition]));

        $dataSource
            ->expects($this->once())
            ->method('getQueryBuilder')
            ->will($this->returnValue($qb));

        $this->extension->visitDatasource($config, $dataSource);
        $this->assertFalse($this->extension->isApplicable($config));
    }

    public function testIsApplicableNoMixin()
    {
        $config = $this
            ->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration')
            ->disableOriginalConstructor()
            ->getMock();
        $config
            ->expects($this->any())
            ->method('offsetGetByPath')
            ->will(
                $this->returnValueMap(
                    [
                        [Builder::DATASOURCE_TYPE_PATH, null, OrmDatasource::TYPE],
                        [MarketingListExtension::OPTIONS_MIXIN_PATH, false, false]
                    ]
                )
            );
        $this->assertFalse($this->extension->isApplicable($config));
    }

    /**
     * @dataProvider applicableDataProvider
     *
     * @param int|null $marketingListId
     * @param object|null $marketingList
     * @param bool $expected
     */
    public function testIsApplicable($marketingListId, $marketingList, $expected)
    {
        $gridName = 'test_grid';
        $config = $this->assertIsApplicable($marketingListId, $marketingList, $gridName, true);

        $this->assertEquals($expected, $this->extension->isApplicable($config));
    }

    /**
     * @return array
     */
    public function applicableDataProvider()
    {
        $nonManualMarketingList = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();
        $nonManualMarketingList->expects($this->once())
            ->method('isManual')
            ->will($this->returnValue(false));

        $manualMarketingList = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();
        $manualMarketingList->expects($this->once())
            ->method('isManual')
            ->will($this->returnValue(true));

        return [
            [null, null, false],
            [1, null, false],
            [2, $manualMarketingList, false],
            [3, $nonManualMarketingList, true]
        ];
    }

    /**
     * @param array $dqlParts
     * @param bool  $isMixin
     * @param bool  $expected
     *
     * @dataProvider dataSourceDataProvider
     */
    public function testVisitDatasource($dqlParts, $isMixin, $expected)
    {
        $marketingListId = 1;
        $nonManualMarketingList = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();
        if ($isMixin) {
            $nonManualMarketingList->expects($this->once())
                ->method('isManual')
                ->will($this->returnValue(false));
        } else {
            $nonManualMarketingList->expects($this->never())
                ->method('isManual');
        }
        $gridName = 'test_grid';
        $config = $this->assertIsApplicable($marketingListId, $nonManualMarketingList, $gridName, $isMixin);

        $dataSource = $this
            ->getMockBuilder('Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource')
            ->disableOriginalConstructor()
            ->getMock();

        $qb = $this->getQbMock();

        if (!empty($dqlParts['where'])) {
            /** @var Andx $where */
            $where = $dqlParts['where'];
            $parts = $where->getParts();

            if ($expected) {
                $qb
                    ->expects($this->exactly(sizeof($parts)))
                    ->method('andWhere');
            }

            $functionParts = array_filter(
                $parts,
                function ($part) {
                    return !is_string($part);
                }
            );

            if ($functionParts && $expected) {
                $qb
                    ->expects($this->once())
                    ->method('setParameter')
                    ->with($this->equalTo('marketingListId'), $this->equalTo($marketingListId));
            }
        }

        if ($expected) {
            $qb
                ->expects($this->once())
                ->method('getDQLParts')
                ->will($this->returnValue($dqlParts));

            $dataSource
                ->expects($this->once())
                ->method('getQueryBuilder')
                ->will($this->returnValue($qb));
        }

        $this->extension->visitDatasource($config, $dataSource);
    }

    /**
     * @param int|null $marketingListId
     * @param object|null $marketingList
     * @param string $gridName
     * @param bool $isMixin
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function assertIsApplicable($marketingListId, $marketingList, $gridName, $isMixin)
    {
        $config = $this
            ->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration')
            ->disableOriginalConstructor()
            ->getMock();
        $config
            ->expects($this->any())
            ->method('offsetGetByPath')
            ->will(
                $this->returnValueMap(
                    [
                        ['[name]', null, $gridName],
                        [Builder::DATASOURCE_TYPE_PATH, null, OrmDatasource::TYPE],
                        [MarketingListExtension::OPTIONS_MIXIN_PATH, false, $isMixin]
                    ]
                )
            );
        $this->marketingListHelper->expects($this->any())
            ->method('getMarketingListIdByGridName')
            ->with($gridName)
            ->will($this->returnValue($marketingListId));
        if ($marketingListId) {
            $this->marketingListHelper->expects($this->any())
                ->method('getMarketingList')
                ->with($marketingListId)
                ->will($this->returnValue($marketingList));
        } else {
            $this->marketingListHelper->expects($this->never())
                ->method('getMarketingList');
        }

        return $config;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getQbMock()
    {
        $qb = $this
            ->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $qb
            ->expects($this->any())
            ->method('from')
            ->will($this->returnSelf());

        $qb
            ->expects($this->any())
            ->method('leftJoin')
            ->will($this->returnSelf());

        $qb
            ->expects($this->any())
            ->method('select')
            ->will($this->returnSelf());

        $expr = $this
            ->getMockBuilder('Doctrine\ORM\Query\Expr')
            ->disableOriginalConstructor()
            ->getMock();

        $qb
            ->expects($this->any())
            ->method('expr')
            ->will($this->returnValue($expr));

        $orX = $this
            ->getMockBuilder('Doctrine\ORM\Query\Expr')
            ->disableOriginalConstructor()
            ->getMock();

        $expr
            ->expects($this->any())
            ->method('orX')
            ->will($this->returnValue($orX));

        return $qb;
    }

    /**
     * @return array
     */
    public function dataSourceDataProvider()
    {
        return [
            [['where' => []], false, false],
            [['where' => []], true, true],
            [['where' => new Andx()], false, false],
            [['where' => new Andx()], true, true],
            [['where' => new Andx(['test'])], false, false],
            [['where' => new Andx(['test'])], true, true],
            [['where' => new Andx([new Func('func condition', ['argument'])])], false, false],
            [['where' => new Andx([new Func('func condition', ['argument'])])], true, true],
            [['where' => new Andx(['test', new Func('func condition', ['argument'])])], false, false],
            [['where' => new Andx(['test', new Func('func condition', ['argument'])])], true, true],
        ];
    }

    public function testGetPriority()
    {
        $this->assertInternalType('integer', $this->extension->getPriority());
    }
}
