<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\EventListener;

use OroCRM\Bundle\MagentoBundle\EventListener\AccountWidgetsDataGridListener;

class AccountWidgetsDataGridListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AccountWidgetsDataGridListener
     */
    protected $target;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestParams;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataSource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataGrid;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $queryBuilder;

    public function setUp()
    {
        $this->requestParams = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\RequestParameters')
            ->disableOriginalConstructor()
            ->getMock();
        $this->event = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Event\BuildAfter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataGrid = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function init($params = array(), $dataSourceInstants = null)
    {
        $this->dataSource = $dataSourceInstants ? $dataSourceInstants :
            $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource')
                ->disableOriginalConstructor()
                ->getMock();

        $this->dataGrid->expects($this->any())->method('getDatasource')->will($this->returnValue($this->dataSource));
        $this->event->expects($this->any())->method('getDatagrid')->will($this->returnValue($this->dataGrid));
        $this->dataSource->expects($this->any())
            ->method('getQueryBuilder')
            ->will($this->returnValue($this->queryBuilder));
        $this->target = new AccountWidgetsDataGridListener($this->requestParams, $params);
    }

    public function testOnBuildAfterSetParams()
    {
        $id = rand();
        $name = 'name_'.rand();
        $this->requestParams->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    array(
                        array('id', null, $id),
                        array('name', null, $name)
                    )
                )
            );
        $keys = array('id', 'name');
        $expected = array('id' => $id, 'name' => $name);
        $this->init($keys);
        $this->queryBuilder->expects($this->once())->method('setParameters')->with($this->equalTo($expected));
        $this->target->onBuildAfter($this->event);
    }

    public function testOnBuildAfterChecksDataSourceType()
    {
        $dataSource = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->init(array(), $dataSource);
        $this->queryBuilder->expects($this->never())->method('setParameters');
        $this->target->onBuildAfter($this->event);
    }
}
