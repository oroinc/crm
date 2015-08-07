<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Form\Handler;

use Doctrine\Common\Collections\ArrayCollection;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Form\Handler\CartHandler;

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

        $registry = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');

        $this->manager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $registry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($this->manager));

        $security     = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')
            ->setMethods(['getToken'])
            ->disableOriginalConstructor()
            ->getMock();
        $token        = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\Token')
            ->setMethods(['getOrganizationContext'])
            ->disableOriginalConstructor()
            ->getMock();
        $organization = $this->getMockBuilder('Oro\Bundle\OrganizationBundle\Entity\Organization')
            ->disableOriginalConstructor()
            ->getMock();

        $token->expects($this->once())
            ->method('getOrganizationContext')
            ->will($this->returnValue($organization));

        $security->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));

        $this->entity  = new Cart();
        $this->handler = new CartHandler($this->form, $this->request, $registry, $security);
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

        $cartItem = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Entity\CartItem')
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
