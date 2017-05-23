<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;

use Oro\Bundle\MagentoBundle\Provider\TransportEntityProvider;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

class TransportEntityProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var  TransportEntityProvider */
    protected $transportEntityProvider;

    /** @var  EntityManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $entityManager;

    /** @var  FormFactoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $formFactory;

    /** @var  FormInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $form;

    /** @var  Request|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var  MagentoTransport */
    protected $transportEntity;

    public function setUp()
    {
        $this->formFactory      = $this->createMock(FormFactoryInterface::class);
        $this->form             = $this->createMock(FormInterface::class);
        $this->entityManager    = $this->createMock(EntityManager::class);
        $this->request          = $this->createMock(Request::class);
        $this->transportEntity  = $this->createMock(MagentoTransport::class);

        $this->transportEntityProvider = new TransportEntityProvider(
            $this->formFactory,
            $this->entityManager
        );
    }

    public function tearDown()
    {
        parent::tearDown();
        unset(
            $this->transportEntityProvider,
            $this->formFactory,
            $this->form,
            $this->request
        );
    }

    public function testGetTransportEntityByRequest()
    {
        $this->entityManager
             ->expects($this->once())
             ->method('find')
             ->willReturn($this->transportEntity);

        $this->request
             ->expects($this->once())
             ->method('get')
             ->will($this->returnArgument(0));

        $this->form
             ->expects($this->once())
             ->method('getData')
             ->willReturn($this->transportEntity);

        $this->form
             ->expects($this->once())
             ->method('handleRequest');

        $this->formFactory
             ->expects($this->once())
             ->method('createNamed')
             ->willReturn($this->form);

        $transport = $this->createMock(MagentoTransportInterface::class);
        $transport->expects($this->once())
                  ->method('getSettingsEntityFQCN')
                  ->willReturn('test');

        $entity = $this->transportEntityProvider->getTransportEntityByRequest($transport, $this->request);

        $this->assertEquals($this->transportEntity, $entity);
    }

    public function testFindTransportEntityByObject()
    {
        $transport = $this->createMock(MagentoTransportInterface::class);
        $transport->expects($this->once())
                  ->method('getSettingsEntityFQCN')
                  ->willReturn('test');

        $this->entityManager
             ->expects($this->once())
             ->method('find')
             ->willReturn($this->transportEntity);

        $entity = $this->transportEntityProvider->findTransportEntity($transport, 'test');

        $this->assertEquals($entity, $this->transportEntity);
    }

    public function testFindTransportEntityByString()
    {
        $this->entityManager
            ->expects($this->once())
            ->method('find')
            ->willReturn($this->transportEntity);

        $entity = $this->transportEntityProvider->findTransportEntity('test', 'test');

        $this->assertEquals($entity, $this->transportEntity);
    }

    /**
     * @expectedException \LogicException
     */
    public function testFindTransportEntityByUnExpected()
    {
        $stubErrorClass = new \stdClass();

        $this->transportEntityProvider->findTransportEntity($stubErrorClass, 'test');
    }
}
