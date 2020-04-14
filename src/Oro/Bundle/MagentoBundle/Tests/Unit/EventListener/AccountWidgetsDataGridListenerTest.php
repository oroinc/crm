<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\EventListener;

use Oro\Bundle\MagentoBundle\EventListener\AccountWidgetsDataGridListener;

class AccountWidgetsDataGridListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AccountWidgetsDataGridListener
     */
    protected $target;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $parameters;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $event;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $dataSource;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $dataGrid;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $queryBuilder;

    protected function setUp(): void
    {
        $this->parameters = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataGrid = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->dataGrid->expects($this->any())
            ->method('getParameters')
            ->will($this->returnValue($this->parameters));

        $this->event = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Event\BuildAfter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->event->expects($this->any())
            ->method('getDatagrid')
            ->will($this->returnValue($this->dataGrid));

        $this->dataSource = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testOnBuildAfterWithOrmDataSource()
    {
        $this->dataGrid->expects($this->once())
            ->method('getDatasource')
            ->will($this->returnValue($this->dataSource));

        $this->dataSource->expects($this->once())
            ->method('getQueryBuilder')
            ->will($this->returnValue($this->queryBuilder));

        $id = rand();
        $name = 'name_'.rand();
        $this->parameters->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    array(
                        array('id', null, $id),
                        array('name', null, $name)
                    )
                )
            );

        $parameters = array('id', 'name');
        $expectedParams = array('id' => $id, 'name' => $name);

        $this->queryBuilder->expects($this->once())
            ->method('setParameters')
            ->with($this->equalTo($expectedParams));

        $this->createListener($parameters)->onBuildAfter($this->event);
    }

    public function testOnBuildAfterChecksDataSourceType()
    {
        $dataSource = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->dataGrid->expects($this->once())
            ->method('getDatasource')
            ->will($this->returnValue($dataSource));

        $this->queryBuilder->expects($this->never())->method('setParameters');
        $this->createListener(array())->onBuildAfter($this->event);
    }

    /**
     * @param array $params
     * @return AccountWidgetsDataGridListener
     */
    protected function createListener(array $params)
    {
        return new AccountWidgetsDataGridListener($params);
    }
}
