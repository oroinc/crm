<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Form\Handler;

use Oro\Bundle\AddressBundle\Entity\AddressType;

use OroCRM\Bundle\MagentoBundle\Entity\CartAddress;
use OroCRM\Bundle\MagentoBundle\Form\Handler\CartAddressHandler;

class CartAddressHandlerTest extends AbstractHandlerTest
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


        $this->entity  = new CartAddress();
        $this->handler = new CartAddressHandler($this->form, $this->request, $registry, $security);
    }

    public function testAddCartAddressOnSuccess()
    {
        $cart = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Entity\Cart')
            ->disableOriginalConstructor()
            ->getMock();

        $cart->expects($this->once())
            ->method('setBillingAddress')
            ->with($this->entity);

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

        $this->manager->expects($this->at(0))
            ->method('persist')
            ->with($cart);

        $this->manager->expects($this->at(1))
            ->method('persist')
            ->with($this->entity);

        $this->manager->expects($this->once())
            ->method('flush');

        $this->assertTrue($this->handler->process($this->entity, $cart, AddressType::TYPE_BILLING));
    }
}
