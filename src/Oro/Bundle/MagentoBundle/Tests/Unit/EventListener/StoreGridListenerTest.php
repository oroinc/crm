<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\EventListener\Datagrid;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use Oro\Bundle\MagentoBundle\EventListener\StoreGridListener;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class StoreGridListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var StoreGridListener */
    protected $listener;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $authorizationChecker;

    /** @var string */
    protected $dataChannelClass;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $entityManager;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $datagrid;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $qb;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $datasource;

    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->dataChannelClass = 'Oro\Bundle\IntegrationBundle\Entity\Channel';
        $this->entityManager = $this->createMock(EntityManager::class);

        $this->listener = new StoreGridListener(
            $this->authorizationChecker,
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

    public function testWebsiteConditionForStores()
    {
        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('oro_integration_assign')
            ->will($this->returnValue(true));

        $this->datagrid->expects($this->once())
            ->method('getDatasource')
            ->will($this->returnValue($this->datasource));

        $this->datasource->expects($this->once())
            ->method('getQueryBuilder')
            ->will($this->returnValue($this->qb));

        $this->qb->expects($this->once())
            ->method('andWhere')
            ->with('w.originId = :id')
            ->will($this->returnSelf());

        $this->datagrid->expects($this->any())
            ->method('getParameters')
            ->will($this->returnValue($this->createParameterBag(['channelIds' => 100 ])));

        $transport = new MagentoSoapTransport();
        $integration = new Integration();
        $transport->setWebsiteId(1);
        $integration->setTransport($transport);
        $cnannel = new Channel();
        $cnannel->setDataSource($integration);

        $this->entityManager->expects($this->any())
            ->method('find')
            ->will($this->returnValue($cnannel));

        $event = new BuildAfter($this->datagrid);
        $this->listener->onBuildAfter($event);
    }

    public function testOnBuildAfterAclIntegrationAssignGranted()
    {
        $this->authorizationChecker->expects($this->once())
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
        $this->authorizationChecker->expects($this->once())
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
     * @return \PHPUnit\Framework\MockObject\MockObject
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
