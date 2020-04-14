<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use Oro\Bundle\MagentoBundle\Provider\TransportEntityProvider;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class TransportEntityProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var  TransportEntityProvider */
    protected $transportEntityProvider;

    /** @var  ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    protected $registry;

    /** @var  FormFactoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $formFactory;

    /** @var  FormInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $form;

    /** @var  Request|\PHPUnit\Framework\MockObject\MockObject */
    protected $request;

    /** @var  MagentoSoapTransport */
    protected $transportEntity;

    protected function setUp(): void
    {
        $this->formFactory      = $this->createMock(FormFactoryInterface::class);
        $this->form             = $this->createMock(FormInterface::class);
        $this->registry         = $this->createMock(ManagerRegistry::class);
        $this->request          = $this->createMock(Request::class);
        $this->transportEntity  = $this->createMock(MagentoSoapTransport::class);

        $this->transportEntityProvider = new TransportEntityProvider(
            $this->formFactory,
            $this->registry
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset(
            $this->transportEntityProvider,
            $this->formFactory,
            $this->form,
            $this->request
        );
    }

    /**
     * @dataProvider testGetTransportEntityByRequestProvider
     *
     * @param bool $doFind
     */
    public function testGetTransportEntityByRequest($doFind)
    {
        $entityId = 1;
        $fqcn = 'test';
        $em = $this->createMock(ObjectManager::class);

        if ($doFind) {
            $em
                ->expects($this->once())
                ->method('find')
                ->with($fqcn, $entityId)
                ->willReturn(true);

            $this->registry
                ->expects($this->atLeastOnce())
                ->method('getManagerForClass')
                ->willReturn($em);
        } else {
            $em->expects($this->never())->method('find');
            $this->registry->expects($this->never())->method('getManagerForClass');
        }

        $this->request
            ->expects($this->once())
            ->method('get')
            ->willReturnCallback(function ($arg) use ($entityId, $doFind) {
                if ($arg === 'id' && $doFind) {
                    return $entityId;
                }

                return null;
            });

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
        $transport->method('getSettingsEntityFQCN')->willReturn($fqcn);

        $entity = $this->transportEntityProvider->getTransportEntityByRequest($transport, $this->request);

        $this->assertEquals($this->transportEntity, $entity);
    }

    public function testGetTransportEntityByRequestProvider()
    {
        return [
            'From update page' => [
                'doFind' => true
            ],
            'From create page' => [
                'doFind' => false
            ]
        ];
    }
}
