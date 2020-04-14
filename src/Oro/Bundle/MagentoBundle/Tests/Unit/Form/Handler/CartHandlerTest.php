<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Form\Handler\CartHandler;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CartHandlerTest extends AbstractHandlerTest
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
        $organization = $this->createMock(Organization::class);
        $tokenAccessor->expects($this->once())
            ->method('getOrganization')
            ->will($this->returnValue($organization));

        $this->entity  = new Cart();
        $this->handler = new CartHandler($this->form, $requestStack, $registry, $tokenAccessor);
    }

    public function testProcessOnSuccess()
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

        $cartItem = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Entity\CartItem')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entity->setCartItems(new ArrayCollection([$cartItem]));

        $cartItem->expects($this->once())
            ->method('setCart')
            ->with($this->entity);

        $this->assertTrue($this->handler->process($this->entity));

        $this->assertEquals(1, $this->entity->getItemsCount());
    }
}
