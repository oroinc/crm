<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Form\Handler\OrderHandler;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class OrderHandlerTest extends AbstractHandlerTest
{
    protected function setUp(): void
    {
        $this->form = $this->createMock(Form::class);
        $this->request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($this->request);
        $registry = $this->createMock(ManagerRegistry::class);
        $this->manager = $this->createMock(ObjectManager::class);

        $registry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($this->manager));

        $tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $organization = $this->getMockBuilder('Oro\Bundle\OrganizationBundle\Entity\Organization')
            ->disableOriginalConstructor()
            ->getMock();
        $tokenAccessor->expects($this->once())
            ->method('getOrganization')
            ->will($this->returnValue($organization));

        $this->entity  = new Order();
        $this->handler = new OrderHandler($this->form, $requestStack, $registry, $tokenAccessor);
    }

    public function testValidProcess()
    {
        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);

        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod('POST');

        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);

        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->manager->expects($this->once())
            ->method('persist')
            ->with($this->entity);

        $this->manager->expects($this->once())
            ->method('flush');

        $address = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Entity\OrderAddress')
            ->disableOriginalConstructor()
            ->getMock();

        $address->expects($this->once())
            ->method('setOwner')
            ->with($this->entity);

        $this->entity->addAddress($address);

        $item = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Entity\OrderItem')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entity->setItems(new ArrayCollection([$item]));

        $item->expects($this->once())
            ->method('setOrder')
            ->with($this->entity);

        $this->assertTrue($this->handler->process($this->entity));
    }
}
