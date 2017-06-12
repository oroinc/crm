<?php

namespace Oro\Bundle\CalendarBundle\Tests\Unit\EventListener\Datagrid;

use Oro\Bundle\DataGridBundle\Event\BuildAfter;

use OroCRM\Bundle\MagentoBundle\EventListener\CustomerGroupGridListener;

class CustomerGroupGridListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var StoreGridListener */
    protected $listener;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $securityFacade;

    /** @var string */
    protected $dataChannelClass;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $entityManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $datagrid;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $qb;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $datasource;

    protected function setUp()
    {
        $this->securityFacade = $this->getMockBuilder('Oro\Bundle\SecurityBundle\SecurityFacade')
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataChannelClass = 'Oro\Bundle\IntegrationBundle\Entity\Channel';

        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();

        $this->listener = new CustomerGroupGridListener(
            $this->securityFacade,
            $this->dataChannelClass,
            $this->entityManager
        );

        $this->datagrid = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface')
            ->getMock();

        $this->qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->datasource = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testOnBuildAfterAclIntegrationAssignGranted()
    {
        $this->securityFacade->expects($this->once())
            ->method('isGranted')
            ->with('oro_integration_assign')
            ->will($this->returnValue(true));

        $this->datagrid->expects($this->once())
            ->method('getDatasource')
            ->will($this->returnValue($this->datasource));

        $this->datagrid->expects($this->any())
            ->method('getParameters')
            ->will($this->returnValue($this->createParameterBag(['channelIds' => 100 ])));

        $this->qb->expects($this->never())
            ->method('andWhere')
            ->with('1 = 0')
            ->will($this->returnSelf());

        $event = new BuildAfter($this->datagrid);
        $this->listener->onBuildAfter($event);
    }

    public function testOnBuildAfterAclIntegrationAssignNotGranted()
    {
        $this->securityFacade->expects($this->once())
            ->method('isGranted')
            ->with('oro_integration_assign')
            ->will($this->returnValue(false));

        $this->datasource->expects($this->once())
            ->method('getQueryBuilder')
            ->will($this->returnValue($this->qb));

        $this->datagrid->expects($this->once())
            ->method('getDatasource')
            ->will($this->returnValue($this->datasource));

        $this->qb->expects($this->at(0))
            ->method('andWhere')
            ->with('1 = 0')
            ->will($this->returnSelf());

        $event = new BuildAfter($this->datagrid);
        $this->listener->onBuildAfter($event);
    }

    /**
     * @param array $data
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createParameterBag(array $data)
    {
        $parameters = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\ParameterBag')->getMock();

        $parameters->expects($this->any())
            ->method('get')
            ->will(
                $this->returnCallback(
                    function ($key) use ($data) {
                        return $data[$key];
                    }
                )
            );

        return $parameters;
    }
}
