<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Handler;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\MagentoBundle\Entity\CartAddress;
use Oro\Bundle\MagentoBundle\Form\Handler\CartAddressHandler;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CartAddressHandlerTest extends AbstractHandlerTest
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

        $this->entity  = new CartAddress();
        $this->handler = new CartAddressHandler($this->form, $requestStack, $registry, $tokenAccessor);
    }

    public function testAddCartAddressOnSuccess()
    {
        $cart = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Entity\Cart')
            ->disableOriginalConstructor()
            ->getMock();

        $cart->expects($this->once())
            ->method('setBillingAddress')
            ->with($this->entity);

        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);

        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod('POST');

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
