<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Handler;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Form\Handler\CartHandler;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

class CartHandlerTest extends AbstractHandlerTest
{
    protected function setUp()
    {
        $this->form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $registry = $this->createMock('Symfony\Bridge\Doctrine\RegistryInterface');

        $this->manager = $this->createMock('Doctrine\Common\Persistence\ObjectManager');

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

        $this->entity  = new Cart();
        $this->handler = new CartHandler($this->form, $this->request, $registry, $tokenAccessor);
    }

    public function testProcessOnSuccess()
    {
        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);

        $this->request->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('POST'));

        $this->form->expects($this->once())
            ->method('submit');

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
